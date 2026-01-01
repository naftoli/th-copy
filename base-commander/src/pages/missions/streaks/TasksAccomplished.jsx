import React, { Component } from 'react';
import { connect } from 'react-redux';
import api from 'api/api';
import { setTitle } from 'functions/utils';
import { Row, Col, Button } from 'reactstrap';
import { Spinner, Callout, InlineSync } from 'components/ui';
import { BaseSelect, PlatoonSelect, SoldierSelect, Select } from 'components/selects';
import { getSoldiers } from 'store/base/soldiers/operations';

// Loads the ApexCharts script from a CDN if it is not already present.
// Returns a Promise that resolves with the global ApexCharts constructor.
function loadApex() {
  return new Promise(function(resolve, reject) {
    if (window.ApexCharts) return resolve(window.ApexCharts);
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
    s.async = true;
    s.onload = function() { resolve(window.ApexCharts); };
    s.onerror = reject;
    document.body.appendChild(s);
  });
}

// Converts a UTC Date into a Julian Day Number (integer days).
// JDN is used by the backend payload; charts need milliseconds so we convert back later.
function jd(date) {
  const a = Math.floor((14 - (date.getUTCMonth() + 1)) / 12);
  const y = date.getUTCFullYear() + 4800 - a;
  const m = (date.getUTCMonth() + 1) + 12 * a - 3;
  const jdn = (date.getUTCDate())
    + Math.floor((153 * m + 2) / 5)
    + 365 * y + Math.floor(y / 4) - Math.floor(y / 100) + Math.floor(y / 400)
    - 32045;
  return jdn;
}

// Converts a Julian Day Number back to a UTC Date at midnight.
function jdToUtcDate(jd) {
  let l = jd + 68569;
  const n = Math.floor(4 * l / 146097);
  l = l - Math.floor((146097 * n + 3) / 4);
  const i = Math.floor(4000 * (l + 1) / 1461001);
  l = l - Math.floor(1461 * i / 4) + 31;
  const j = Math.floor(80 * l / 2447);
  const day = l - Math.floor(2447 * j / 80);
  l = Math.floor(j / 11);
  const month = j + 2 - 12 * l;
  const year = 100 * (n - 49) + i + l;
  return new Date(Date.UTC(year, month - 1, day));
}

// Groups consecutive JDNs into continuous [startMs, endMs) ranges in milliseconds.
// Example: [5,6,7,9] -> [[5,8), [9,10)) in ms (converted via jdToUtcDate).
function groupJdRanges(jds) {
  if (!jds || jds.length === 0) return [];
  const ranges = [];
  let start = jds[0];
  let prev = jds[0];
  for (let idx = 1; idx < jds.length; idx++) {
    const jd = jds[idx];
    if (jd === prev + 1) {
      prev = jd;
    } else {
      const startMs = jdToUtcDate(start).getTime();
      const endMs = jdToUtcDate(prev + 1).getTime();
      ranges.push([startMs, endMs]);
      start = jd;
      prev = jd;
    }
  }
  ranges.push([jdToUtcDate(start).getTime(), jdToUtcDate(prev + 1).getTime()]);
  return ranges;
}

// Same as groupJdRanges, but returns raw JDN ranges [startJd, endJdExclusive].
// This is useful to compute the "missing" ranges by inverting against the full window.
function groupJdRangesRaw(jds) {
  if (!jds || jds.length === 0) return [];
  const raw = [];
  let start = jds[0];
  let prev = jds[0];
  for (let idx = 1; idx < jds.length; idx++) {
    const jd = jds[idx];
    if (jd === prev + 1) prev = jd;
    else {
      raw.push([start, prev + 1]);
      start = jd;
      prev = jd;
    }
  }
  raw.push([start, prev + 1]);
  return raw;
}

// Given a full window [startJd, endJdExclusive) and a list of completed raw ranges,
// returns the complementary list of "missing" raw ranges in JDNs.
function invertRanges(startJd, endJdExclusive, rangesRaw) {
  const missing = [];
  let cursor = startJd;
  rangesRaw.forEach(function(r) {
    const s = r[0], e = r[1];
    if (cursor < s) missing.push([cursor, s]);
    cursor = Math.max(cursor, e);
  });
  if (cursor < endJdExclusive) missing.push([cursor, endJdExclusive]);
  return missing;
}

// Extracts the streak payload from API responses.
// Backend may return either:
// - a direct payload object: { startJd, endJd, tasks: [...] }
// - or an object keyed by user_id: { "12345": { startJd, endJd, tasks: [...] } }
function extractStreakPayload(response, userId) {
  if (!response) return null;
  const hasArrayTasks = Array.isArray(response.tasks);
  const hasObjectTasks = response.tasks && typeof response.tasks === 'object' && !Array.isArray(response.tasks);
  // Already normalized
  if (hasArrayTasks && response.startJd != null && response.endJd != null) return response;
  // New shape: { startJd, endJd, tasks: { [userId]: Task[] } }
  if (hasObjectTasks && response.startJd != null && response.endJd != null) {
    const tasksByUser = response.tasks || {};
    const key = (userId != null && Object.prototype.hasOwnProperty.call(tasksByUser, String(userId)))
      ? String(userId)
      : (function() {
          const ks = Object.keys(tasksByUser);
          return ks.length === 1 ? ks[0] : null;
        })();
    const list = key ? (tasksByUser[key] || []) : [];
    return { startJd: response.startJd, endJd: response.endJd, tasks: list };
  }
  // Older keyed shape: { [userId]: { startJd, endJd, tasks: [] } }
  const uid = userId != null ? String(userId) : null;
  if (uid && Object.prototype.hasOwnProperty.call(response, uid)) {
    return response[uid];
  }
  const keys = Object.keys(response || {});
  if (keys.length === 1) {
    return response[keys[0]];
  }
  return null;
}

class TasksAccomplished extends Component {
  state = {
    subjects: [],
    // identifies where current subjects list came from: 'default' | 'user' | 'class'
    subjectsScope: 'default',
    form: {
      school_id: false,
      class_id: false,
      user_id: '',
      subject_id: '-1',
      start: '',
      end: '',
      date_range: '30',
      order_by: 'campaign'
    },
    payload: null,
    loading: false
  };

  chartCountsByName = {};
  chartRef = null;
  chartsContainerRef = null;
  ApexCharts = null;
  chartObj = null;
  chartObjs = {};

  // Initializes page: sets title, fetches default campaigns, loads ApexCharts and soldiers,
  // and pre-fills selection from the URL if present.
  componentDidMount() {
    setTitle('Tasks Accomplished Page');
    const self = this;
    loadApex().then(function(Apex) { self.ApexCharts = Apex; });
    // load soldiers list for All selection scenarios
    this.props.getSoldiers && this.props.getSoldiers();
    // Prefill user_id from query string if present
    try {
      const params = new URLSearchParams(window.location.search);
      const uid = params.get('user_id') || params.get('id');
      const school = this.props.login && this.props.login.school_id;
      const klass = this.props.login && this.props.login.class_id;
      if (uid) {
        this.setState(function(prev){ 
          return { form: Object.assign({}, prev.form, { user_id: uid, school_id: school || prev.form.school_id, class_id: klass || prev.form.class_id }) };
        });
      } else if (school || klass) {
        this.setState(function(prev){
          return { form: Object.assign({}, prev.form, { school_id: school || prev.form.school_id, class_id: klass || prev.form.class_id }) };
        })
      }
      // Only load subjects if platoon is already selected (e.g., from login)
      if (klass) this.loadSubjectsForClass();
    } catch (e) {}
  }

  // Generic handler for react-select components.
  // - Persists the field by key into form state.
  // - When changing base (school_id), clears platoon/child.
  // - When changing user, (re)loads subject options specific to that child.
  handleSelectChange = (key) => (option) => {
    const value = option ? option.value : false;
    this.setState(function(prev){
      const next = Object.assign({}, prev.form, { [key]: value });
      // if base changes, clear platoon/child
      if (key === 'school_id') {
        next.user_id = false;
        next.class_id = false;
        next.subject_id = '-1';
      }
      return { form: next };
    }, () => {
      // when changing user, load campaigns for that child; when cleared, load default subjects
      if (key === 'user_id') {
        if (this.state.form.user_id) {
          // only fetch if a platoon is selected
          if (this.state.form.class_id) this.loadSubjectsForUser(this.state.form.user_id);
        } else {
          // no user selected; if platoon selected load by class, else clear subjects
          if (this.state.form.class_id) this.loadSubjectsForClass();
          else this.setState({ subjects: [], subjectsScope: 'default', form: Object.assign({}, this.state.form, { subject_id: '-1' }) });
        }
      } else if (key === 'school_id') {
        // after changing base, prefetch by school
        this.loadDefaultSubjects();
      }
    });
  }

  // Platoon change handler. Sets class_id and clears selected child.
  handlePlatoonChange = (option) => {
    const value = option ? option.value : false;
    this.setState(function(prev){
      const next = Object.assign({}, prev.form, { class_id: value, user_id: false });
      return { form: next };
    }, () => {
      // after changing class, if a class is selected load class campaigns; else clear subjects
      if (this.state.form.class_id) this.loadSubjectsForClass();
      else this.setState({ subjects: [], subjectsScope: 'default', form: Object.assign({}, this.state.form, { subject_id: '-1' }) });
    });
  }

  // Basic input handler for native inputs (start/end/…).
  // If order_by changes and a payload is present, re-renders to apply sort immediately.
  handleChange = (e) => {
    const name = e.target.name;
    const value = e.target.value;
    this.setState(function(prev) {
      return { form: Object.assign({}, prev.form, { [name]: value }) };
    }, () => {
      if (name === 'order_by' && this.state.payload) {
        this.renderChart(); // re-sort dynamically
      }
    });
  }

  // Loads the default subjects for the institution (no child-specific restriction).
  loadDefaultSubjects = () => {
    // Fetch subjects by selected school; dropdown remains disabled until platoon is chosen
    const schoolId = this.state.form.school_id;
    if (!schoolId) { this.setState({ subjects: [], subjectsScope: 'default' }); return; }
    api.get('/missions/subjects?school_id=' + String(schoolId))
      .then((subjects) => {
        subjects = subjects || [];
        this.setState(function(prev){
          const allowed = new Set(subjects.map(function(s){ return String(s.subject_id); }));
          let nextSubjectId = prev.form.subject_id;
          if (!allowed.has(String(nextSubjectId))) {
            nextSubjectId = subjects.length ? subjects[0].subject_id : '-1';
          }
          return { 
            subjects: subjects, 
            subjectsScope: 'school',
            form: Object.assign({}, prev.form, { subject_id: nextSubjectId })
          };
        });
      })
      .catch(() => this.setState({ subjects: [], subjectsScope: 'default' }));
  }

  // Loads campaigns (subjects) available to a specific child.
  // If current subject selection is invalid for that child, reset to first allowed or "All" (-1).
  loadSubjectsForUser = (user_id) => {
    // guard: only fetch if platoon is selected
    if (!this.state.form.class_id) return;
    api.get(`/missions/subjects?user_id=${user_id}`)
      .then((subjects) => {
        subjects = subjects || [];
        const allowed = new Set(subjects.map(s => String(s.subject_id)));
        this.setState(function(prev){
          let nextSubjectId = prev.form.subject_id;
          if (!allowed.has(String(nextSubjectId))) {
            nextSubjectId = subjects.length ? subjects[0].subject_id : '-1';
          }
          return {
            subjects,
            subjectsScope: 'user',
            form: Object.assign({}, prev.form, { subject_id: nextSubjectId })
          };
        });
      })
      .catch(() => this.setState({ subjects: [] }));
  }

  // Loads campaigns by aggregating all subjects for the current class's soldiers.
  // Merges unique subjects across class and updates dropdown accordingly.
  loadSubjectsForClass = async () => {
    try {
      const classId = this.state.form.class_id;
      if (!classId) { this.setState({ subjects: [], subjectsScope: 'default' }); return; }
      const subjects = await api.get('/missions/subjects?class_id=' + classId);
      const list = (subjects || []).slice().sort(function(a, b){ return (a.subject_name || '').localeCompare(b.subject_name || ''); });
      this.setState(function(prev){
        const allowed = new Set(list.map(function(s){ return String(s.subject_id); }));
        let nextSubjectId = prev.form.subject_id;
        if (!allowed.has(String(nextSubjectId))) {
          nextSubjectId = list.length ? list[0].subject_id : '-1';
        }
        return {
          subjects: list,
          subjectsScope: 'class',
          form: Object.assign({}, prev.form, { subject_id: nextSubjectId })
        };
      });
    } catch (e) {
      this.setState({ subjects: [], subjectsScope: 'default' });
    }
  }

  // Submits the form:
  // - Derives the date window (JDNs) from start/end or "last N days".
  // - If a specific child is selected, fetches and renders a single chart.
  // - Otherwise, fetches and renders a chart per child in the selected base/platoon.
  // Ensures ApexCharts is loaded before rendering.
  handleSubmit = (e) => {
    e.preventDefault();
    const form = this.state.form;
    this.setState({ loading: true }, async () => {
      try {
        // Ensure ApexCharts is loaded before rendering
        if (!this.ApexCharts) {
          this.ApexCharts = await loadApex();
        }
        let startJd, endJd;
        if (form.start && form.end) {
          startJd = jd(new Date(form.start));
          endJd = jd(new Date(form.end));
        } else {
          const today = new Date();
          endJd = jd(today);
          const days = parseInt(form.date_range || '30', 10);
          startJd = endJd - days;
        }
        // if a specific child selected
        if (form.user_id) {
          const paramsOne = new URLSearchParams({
            user_id: form.user_id,
            subject_id: form.subject_id,
            start_jd: String(startJd),
            end_jd: String(endJd)
          }).toString();
          const dataOneRaw = await api.get('/missions/streaks?' + paramsOne);
          const dataOne = extractStreakPayload(dataOneRaw, form.user_id);
          this.setState({ payload: Object.assign({}, dataOne, { orderBy: form.order_by }) }, () => {
            this.clearCharts();
            this.renderChartForElement(this.chartsContainerRef || this.chartRef, dataOne, form.order_by, null);
          });
        } else {
          // Multi-child: prefer class_id if selected, else school_id
          const idParams = {};
          if (form.class_id) idParams.class_id = form.class_id;
          else if (form.school_id) idParams.school_id = form.school_id;
          // Build request once
          const paramsAll = new URLSearchParams(Object.assign({}, idParams, {
            subject_id: form.subject_id,
            start_jd: String(startJd),
            end_jd: String(endJd)
          })).toString();
          const dataRaw = await api.get('/missions/streaks?' + paramsAll);
          // Prepare children list from current filters for rendering names
          const children = this.getFilteredSoldiers();
          children.sort(function(a, b) {
            const an = (a.first + ' ' + a.last).toLowerCase();
            const bn = (b.first + ' ' + b.last).toLowerCase();
            return an < bn ? -1 : an > bn ? 1 : 0;
          });
          this.clearCharts();
          for (let idx = 0; idx < children.length; idx++) {
            const child = children[idx];
            const data = extractStreakPayload(dataRaw, child.user_id);
            if (!data || !Array.isArray(data.tasks)) continue;
            const wrapper = document.createElement('div');
            wrapper.style.marginBottom = '24px';
            const title = document.createElement('h4');
            title.textContent = child.first + ' ' + child.last;
            const div = document.createElement('div');
            div.id = 'chart-container-' + child.user_id;
            wrapper.appendChild(title);
            wrapper.appendChild(div);
            if (this.chartsContainerRef) this.chartsContainerRef.appendChild(wrapper);
            this.renderChartForElement(div, data, form.order_by, child.user_id);
          }
        }
      } catch (err) {
        console.error(err);
      } finally {
        this.setState({ loading: false });
      }
    });
  }

  // Applies client-side ordering to the tasks for chart display.
  // Supports: completed asc/desc, alphabetical, and mission-sheet (type/label) ordering.
  sortTasks(tasks, orderBy) {
    const arr = tasks.slice();
    if (orderBy === 'completed-asc') {
      arr.sort(function(a, b) { return a.jds.length - b.jds.length; });
    } else if (orderBy === 'completed-desc') {
      arr.sort(function(a, b) { return b.jds.length - a.jds.length; });
    } else if (orderBy === 'alpha') {
      arr.sort(function(a, b) { return (a.name || '').localeCompare(b.name || ''); });
    } else {
      const typeRank = { daily: 0, weekly: 1, shabbos: 2 };
      arr.sort(function(a, b) {
        const ta = (a.type && a.type in typeRank) ? typeRank[a.type] : 99;
        const tb = (b.type && b.type in typeRank) ? typeRank[b.type] : 99;
        if (ta !== tb) return ta - tb;
        const afRaw = (a.frequencyId != null ? a.frequencyId : 9999);
        const bfRaw = (b.frequencyId != null ? b.frequencyId : 9999);
        const af = (a.type === 'daily' && afRaw === 15) ? -1 : afRaw;
        const bf = (b.type === 'daily' && bfRaw === 15) ? -1 : bfRaw;
        if (af !== bf) return af - bf;
        const aLabelId = (a.labelId != null ? a.labelId : null);
        const bLabelId = (b.labelId != null ? b.labelId : null);
        if (aLabelId !== null && aLabelId === bLabelId) {
          const lao = (a.labelOrd != null ? a.labelOrd : 9999);
          const lbo = (b.labelOrd != null ? b.labelOrd : 9999);
          if (lao !== lbo) return lao - lbo;
        }
        const lab = (a.label || '').localeCompare(b.label || '');
        if (lab !== 0) return lab;
        return (a.name || '').localeCompare(b.name || '');
      });
    }
    return arr;
  }

  // Builds series data for ApexCharts from sorted tasks and payload window.
  // Returns accomplished/missing data points and count summaries for y-axis labels.
  buildSeriesFrom(sortedTasks, payload) {
    const dataPoints = [];
    const missingPoints = [];
    const counts = {};
    const totalDaysInWindow = (payload.endJd - payload.startJd + 1);
    sortedTasks.forEach(function(t) {
      const ranges = groupJdRanges(t.jds);
      const raw = groupJdRangesRaw(t.jds);
      const inv = invertRanges(payload.startJd, payload.endJd + 1, raw);
      if (!counts[t.name]) counts[t.name] = { done: 0, total: totalDaysInWindow };
      counts[t.name].done += t.jds.length;
      ranges.forEach(function(r) { dataPoints.push({ x: t.name, y: r }); });
      inv.forEach(function(r) {
        const startMs = jdToUtcDate(r[0]).getTime();
        const endMs = jdToUtcDate(r[1]).getTime();
        missingPoints.push({ x: t.name, y: [startMs, endMs] });
      });
    });
    return { dataPoints: dataPoints, missingPoints: missingPoints, counts: counts, sorted: sortedTasks };
  }

  // Legacy single-chart renderer kept for backwards compatibility with a single container.
  // Uses state.payload and writes into this.chartRef.
  renderChart() {
    // deprecated single-entry usage kept for backwards compatibility
    if (!this.state.payload || !this.ApexCharts) return;
    const payload = this.state.payload;
    const order = this.state.form.order_by || payload.orderBy || 'campaign';
    const sorted = this.sortTasks(payload.tasks || [], order);
    const built = this.buildSeriesFrom(sorted, payload);
    this.chartCountsByName = built.counts;

    const xMin = jdToUtcDate(payload.startJd).getTime();
    const xMax = jdToUtcDate(payload.endJd + 1).getTime();
    const namesSet = {};
    for (let i = 0; i < built.sorted.length; i++) namesSet[built.sorted[i].name] = true;
    const uniqueTasksCount = Object.keys(namesSet).length;
    const ROW_HEIGHT_PX = 30;
    const ROW_GAP_PX = 4;
    const SLOT_PX = ROW_HEIGHT_PX + ROW_GAP_PX;
    const BAR_HEIGHT_PERCENT = Math.max(1, Math.min(100, Math.round((ROW_HEIGHT_PX / SLOT_PX) * 100)));
    const chartHeight = Math.max(200, uniqueTasksCount * SLOT_PX + 60);

    const options = {
      series: [
        { name: 'Missing', data: built.missingPoints },
        { name: 'Accomplished', data: built.dataPoints }
      ],
      chart: { type: 'rangeBar', height: chartHeight, toolbar: { show: false }, zoom: { enabled: false }, selection: { enabled: false } },
      plotOptions: { bar: { horizontal: true, barHeight: BAR_HEIGHT_PERCENT + '%', rangeBarGroupRows: true } },
      xaxis: { type: 'datetime', min: xMin, max: xMax, labels: { format: 'MMM d' } },
      yaxis: {
        labels: {
          style: { fontSize: '12px' },
          formatter: (function(self) {
            return function(val) {
              var m = self.chartCountsByName[val];
              if (!m) return val;
              return val + ' (' + m.done + ' / ' + m.total + ')';
            }
          })(this)
        }
      },
      colors: ['#e0e0e0', '#2ecc71'],
      dataLabels: { enabled: false },
      grid: { xaxis: { lines: { show: true } }, padding: { right: 0 } },
      tooltip: { x: { format: 'MMM d' } }
    };

    if (this.chartObj) {
      this.chartObj.updateOptions(options, true, true);
    } else if (this.chartRef && this.chartRef.id === 'chart-container' && this.ApexCharts) {
      this.chartObj = new this.ApexCharts(this.chartRef, options);
      this.chartObj.render();
    }
  }

  // Generic chart renderer that writes into a specific DOM element.
  // Used both for the single "current child" chart and for the per-child charts case.
  renderChartForElement(targetEl, payload, orderBy, key) {
    if (!payload || !this.ApexCharts || !targetEl) return;
    const sorted = this.sortTasks(payload.tasks || [], orderBy || 'campaign');
    const built = this.buildSeriesFrom(sorted, payload);
    const xMin = jdToUtcDate(payload.startJd).getTime();
    const xMax = jdToUtcDate(payload.endJd + 1).getTime();
    const namesSet = {};
    for (let i = 0; i < built.sorted.length; i++) namesSet[built.sorted[i].name] = true;
    const uniqueTasksCount = Object.keys(namesSet).length;
    const ROW_HEIGHT_PX = 30;
    const ROW_GAP_PX = 4;
    const SLOT_PX = ROW_HEIGHT_PX + ROW_GAP_PX;
    const BAR_HEIGHT_PERCENT = Math.max(1, Math.min(100, Math.round((ROW_HEIGHT_PX / SLOT_PX) * 100)));
    const chartHeight = Math.max(200, uniqueTasksCount * SLOT_PX + 60);

    const options = {
      series: [
        { name: 'Missing', data: built.missingPoints },
        { name: 'Accomplished', data: built.dataPoints }
      ],
      chart: { type: 'rangeBar', height: chartHeight, toolbar: { show: false }, zoom: { enabled: false }, selection: { enabled: false } },
      plotOptions: { bar: { horizontal: true, barHeight: BAR_HEIGHT_PERCENT + '%', rangeBarGroupRows: true } },
      xaxis: { type: 'datetime', min: xMin, max: xMax, labels: { format: 'MMM d' } },
      yaxis: {
        labels: {
          style: { fontSize: '12px' },
          formatter: function (val) {
            var m = built.counts[val];
            if (!m) return val;
            return val + ' (' + m.done + ' / ' + m.total + ')';
          }
        }
      },
      colors: ['#e0e0e0', '#2ecc71'],
      dataLabels: { enabled: false },
      grid: { xaxis: { lines: { show: true } }, padding: { right: 0 } },
      tooltip: { x: { format: 'MMM d' } }
    };

    const chart = new this.ApexCharts(targetEl, options);
    chart.render();
    if (key) this.chartObjs[key] = chart;
  }

  // Ref setter for the multi-chart container (used when rendering multiple children).
  setChartsContainerRef = (el) => {
    this.chartsContainerRef = el;
  }

  // Destroys any existing ApexCharts instances and clears the container DOM.
  clearCharts() {
    // clear old charts
    if (this.chartObj) { try { this.chartObj.destroy(); } catch(e) {} this.chartObj = null; }
    for (const k in this.chartObjs) {
      try { this.chartObjs[k].destroy(); } catch(e) {}
      delete this.chartObjs[k];
    }
    if (this.chartsContainerRef) this.chartsContainerRef.innerHTML = '';
  }

  // Returns soldiers filtered by selected base (required) and optional platoon.
  getFilteredSoldiers() {
    const soldiers = this.props.soldiers || [];
    const schoolId = this.state.form.school_id;
    const classId = this.state.form.class_id || false;
    // filter
    if (!schoolId) return [];
    let s = soldiers.filter(function(sol){ return sol.school_id === String(schoolId) && (!!sol.class_id); });
    if (classId) s = s.filter(function(sol){ return sol.class_id === String(classId); });
    // registered only (optional) - keeping all for streaks; uncomment if needed:
    // s = s.filter(sol => !!sol.user_registered);
    return s;
  }

  // Ref setter for the single-chart container.
  setChartRef = (el) => {
    this.chartRef = el;
  }

  // Renders the Tasks Accomplished page form and containers for the chart(s).
  render() {
    const subjects = this.state.subjects || [];
    const form = this.state.form;
    const loading = this.state.loading;
    return (
      <div id="StreaksPage">
        <Callout title="Tasks Accomplished">
          <p>View accomplished vs missing days for selected missions over a date range.</p>
        </Callout>

        <form onSubmit={ this.handleSubmit }>
          <Row>
            <Col sm="4">
              <label>Base</label>
              <BaseSelect
                required
                value={ form.school_id }
                onChange={ this.handleSelectChange('school_id') } />
            </Col>
            <Col sm="4">
              <label>Platoon</label>
              <PlatoonSelect
                isClearable
                value={ form.class_id }
                schoolId={ form.school_id }
                openMenuOnFocus={ false }
                placeholder='All Platoons'
                onChange={ this.handlePlatoonChange } />
            </Col>
            <Col sm="4">
              <label>Soldier</label>
              <SoldierSelect
                key={(form.school_id || '0') + '-' + (form.class_id || '0')}
                isClearable
                registeredOnly
                showAllOption
                onlyReloadSoldiersIfNotLoaded
                value={ form.user_id }
                classId={ form.class_id }
                schoolId={ form.school_id }
                openMenuOnFocus={ false }
                placeholder='All Soldiers'
                onChange={ this.handleSelectChange('user_id') } />
            </Col>
          </Row>
          <Row style={{ height: 10 }} />
          <Row>
            <Col sm="4">
              <label>Start</label>
              <input type="date" name="start" value={form.start} onChange={this.handleChange} className="form-control" />
            </Col>
            <Col sm="4">
              <label>End</label>
              <input type="date" name="end" value={form.end} onChange={this.handleChange} className="form-control" />
            </Col>
            <Col sm="4">
              <label>Or last Number of days</label>
              {(() => {
                const lastNDaysOptions = [
                  { value: '7', label: '7' },
                  { value: '30', label: '30' },
                  { value: '60', label: '60' },
                  { value: '90', label: '90' },
                ];
                const selected = lastNDaysOptions.find(o => String(o.value) === String(form.date_range)) || null;
                return (
                  <Select
                    options={ lastNDaysOptions }
                    value={ selected }
                    onChange={ this.handleSelectChange('date_range') }
                  />
                );
              })()}
            </Col>
          </Row>
          <Row style={{ marginTop: 12 }}>
            <Col sm="6">
              <label>Campaign</label>
              {(() => {
                const opts = (subjects || []).map(function(s){ 
                  return { value: s.subject_id, label: s.subject_name }; 
                });
                const selected = opts.find(function(o){ return String(o.value) === String(form.subject_id); }) || null;
                const hasSchool = !!this.state.form.school_id;
                return (
                  <Select
                    key={'scope-' + String(this.state.subjectsScope) + '-' + String(this.state.form.user_id || this.state.form.class_id || 'none') + '-' + String(opts.length)}
                    options={ opts }
                    value={ selected }
                    onChange={ this.handleSelectChange('subject_id') }
                    isDisabled={ !hasSchool }
                    placeholder={ hasSchool ? (opts.length ? 'Select campaign' : 'No campaigns') : 'Select a base first' }
                    isClearable={ false }
                  />
                );
              })()}
            </Col>
            <Col sm="6">
              <label>Sort</label>
              {(() => {
                const orderOptions = [
                  { value: 'campaign', label: 'Mission sheet order' },
                  { value: 'completed-asc', label: 'Completed (ascending)' },
                  { value: 'completed-desc', label: 'Completed (descending)' },
                  { value: 'alpha', label: 'Task name (A–Z)' },
                ];
                const selected = orderOptions.find(o => o.value === form.order_by) || null;
                return (
                  <Select
                    options={ orderOptions }
                    value={ selected }
                    onChange={ this.handleSelectChange('order_by') }
                  />
                );
              })()}
            </Col>
          </Row>

          <Row className="buttons" style={{ marginTop: 15 }}>
            <Col xs="12" className="text-center">
              <Button color="primary" type="submit" disabled={loading}>
                <InlineSync loading={ loading } /> Show Tasks
              </Button>
            </Col>
          </Row>
        </form>

        <hr/>

        { loading && <Spinner /> }

        <div className="streaks">
          <div id="chart-container" ref={this.setChartRef} />
          <div id="charts" ref={this.setChartsContainerRef} />
        </div>
      </div>
    );
  }
}

const mapStateToProps = ({ login, base }) => ({
  login: login.current_login,
  soldiers: base && base.soldiers && base.soldiers.soldiers
});

export default connect(mapStateToProps, { getSoldiers })(TasksAccomplished);


