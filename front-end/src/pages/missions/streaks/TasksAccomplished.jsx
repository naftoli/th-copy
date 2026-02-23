import React, { useState, useEffect, useRef } from 'react';
import { connect } from 'react-redux';
import api from 'api/api';
import { setTitle } from 'functions/utils';
import { Row, Col, Button } from 'reactstrap';
import { Spinner, Callout, InlineSync } from 'components/ui';
import { BaseSelect, PlatoonSelect, SoldierSelect, Select } from 'components/selects';
import { getSoldiers } from 'store/base/soldiers/operations';

// Loads the ApexCharts script from a CDN if it is not already present.
function loadApex() {
  return new Promise((resolve, reject) => {
    if (window.ApexCharts) return resolve(window.ApexCharts);
    const s = document.createElement('script');
    s.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
    s.async = true;
    s.onload = () => resolve(window.ApexCharts);
    s.onerror = reject;
    document.body.appendChild(s);
  });
}

// Converts a UTC Date into a Julian Day Number (integer days).
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
      ranges.push([jdToUtcDate(start).getTime(), jdToUtcDate(prev + 1).getTime()]);
      start = jd;
      prev = jd;
    }
  }
  ranges.push([jdToUtcDate(start).getTime(), jdToUtcDate(prev + 1).getTime()]);
  return ranges;
}

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

function invertRanges(startJd, endJdExclusive, rangesRaw) {
  const missing = [];
  let cursor = startJd;
  rangesRaw.forEach(r => {
    const s = r[0], e = r[1];
    if (cursor < s) missing.push([cursor, s]);
    cursor = Math.max(cursor, e);
  });
  if (cursor < endJdExclusive) missing.push([cursor, endJdExclusive]);
  return missing;
}

function extractStreakPayload(response, userId) {
  if (!response) return null;
  const hasArrayTasks = Array.isArray(response.tasks);
  const hasObjectTasks = response.tasks && typeof response.tasks === 'object' && !Array.isArray(response.tasks);
  if (hasArrayTasks && response.startJd != null && response.endJd != null) return response;
  if (hasObjectTasks && response.startJd != null && response.endJd != null) {
    const tasksByUser = response.tasks || {};
    const key = (userId != null && Object.prototype.hasOwnProperty.call(tasksByUser, String(userId)))
      ? String(userId)
      : (() => {
          const ks = Object.keys(tasksByUser);
          return ks.length === 1 ? ks[0] : null;
        })();
    const list = key ? (tasksByUser[key] || []) : [];
    return { startJd: response.startJd, endJd: response.endJd, tasks: list };
  }
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

const TasksAccomplished = ({ login, soldiers, getSoldiers }) => {
  const [subjects, setSubjects] = useState([]);
  const [subjectsScope, setSubjectsScope] = useState('default');
  const [form, setForm] = useState({
    school_id: false,
    class_id: false,
    user_id: '',
    subject_id: '-1',
    start: '',
    end: '',
    date_range: '30',
    order_by: 'campaign'
  });
  const [payload, setPayload] = useState(null);
  const [loading, setLoading] = useState(false);

  const chartCountsByNameRef = useRef({});
  const chartRef = useRef(null);
  const chartsContainerRef = useRef(null);
  const ApexChartsRef = useRef(null);
  const chartObjRef = useRef(null);
  const chartObjsRef = useRef({});

  useEffect(() => {
    setTitle('Tasks Accomplished');
    loadApex().then(Apex => { ApexChartsRef.current = Apex; });
    getSoldiers && getSoldiers();
    
    try {
      const params = new URLSearchParams(window.location.search);
      const uid = params.get('user_id') || params.get('id');
      const school = login?.school_id;
      const klass = login?.class_id;
      
      if (uid) {
        setForm(prev => ({
          ...prev,
          user_id: uid,
          school_id: school || prev.school_id,
          class_id: klass || prev.class_id
        }));
      } else if (school || klass) {
        setForm(prev => ({
          ...prev,
          school_id: school || prev.school_id,
          class_id: klass || prev.class_id
        }));
      }
      
      if (klass) loadSubjectsForClass();
    } catch (e) {
      console.error(e);
    }
  }, []);

  useEffect(() => {
    if (form.user_id) {
      if (form.class_id) loadSubjectsForUser(form.user_id);
    } else {
      if (form.class_id) loadSubjectsForClass();
      else {
        setSubjects([]);
        setSubjectsScope('default');
        setForm(prev => ({ ...prev, subject_id: '-1' }));
      }
    }
  }, [form.user_id]);

  useEffect(() => {
    if (form.school_id) {
      loadDefaultSubjects();
    }
  }, [form.school_id]);

  useEffect(() => {
    if (form.class_id) loadSubjectsForClass();
    else {
      setSubjects([]);
      setSubjectsScope('default');
      setForm(prev => ({ ...prev, subject_id: '-1' }));
    }
  }, [form.class_id]);

  useEffect(() => {
    if (form.order_by && payload) {
      renderChart();
    }
  }, [form.order_by]);

  const handleSelectChange = (key) => (option) => {
    const value = option ? option.value : false;
    setForm(prev => {
      const next = { ...prev, [key]: value };
      if (key === 'school_id') {
        next.user_id = false;
        next.class_id = false;
        next.subject_id = '-1';
      }
      return next;
    });
  };

  const handlePlatoonChange = (option) => {
    const value = option ? option.value : false;
    setForm(prev => ({
      ...prev,
      class_id: value,
      user_id: false
    }));
  };

  const handleChange = (e) => {
    const name = e.target.name;
    const value = e.target.value;
    setForm(prev => ({ ...prev, [name]: value }));
  };

  const loadDefaultSubjects = () => {
    const schoolId = form.school_id;
    if (!schoolId) {
      setSubjects([]);
      setSubjectsScope('default');
      return;
    }
    api.get('/missions/subjects?school_id=' + String(schoolId))
      .then((subjects) => {
        subjects = subjects || [];
        const allowed = new Set(subjects.map(s => String(s.subject_id)));
        let nextSubjectId = form.subject_id;
        if (!allowed.has(String(nextSubjectId))) {
          nextSubjectId = subjects.length ? subjects[0].subject_id : '-1';
        }
        setSubjects(subjects);
        setSubjectsScope('school');
        setForm(prev => ({ ...prev, subject_id: nextSubjectId }));
      })
      .catch(() => {
        setSubjects([]);
        setSubjectsScope('default');
      });
  };

  const loadSubjectsForUser = (user_id) => {
    if (!form.class_id) return;
    api.get(`/missions/subjects?user_id=${user_id}`)
      .then((subjects) => {
        subjects = subjects || [];
        const allowed = new Set(subjects.map(s => String(s.subject_id)));
        let nextSubjectId = form.subject_id;
        if (!allowed.has(String(nextSubjectId))) {
          nextSubjectId = subjects.length ? subjects[0].subject_id : '-1';
        }
        setSubjects(subjects);
        setSubjectsScope('user');
        setForm(prev => ({ ...prev, subject_id: nextSubjectId }));
      })
      .catch(() => setSubjects([]));
  };

  const loadSubjectsForClass = async () => {
    try {
      const classId = form.class_id;
      if (!classId) {
        setSubjects([]);
        setSubjectsScope('default');
        return;
      }
      const subjects = await api.get('/missions/subjects?class_id=' + classId);
      const list = (subjects || []).slice().sort((a, b) => (a.subject_name || '').localeCompare(b.subject_name || ''));
      const allowed = new Set(list.map(s => String(s.subject_id)));
      let nextSubjectId = form.subject_id;
      if (!allowed.has(String(nextSubjectId))) {
        nextSubjectId = list.length ? list[0].subject_id : '-1';
      }
      setSubjects(list);
      setSubjectsScope('class');
      setForm(prev => ({ ...prev, subject_id: nextSubjectId }));
    } catch (e) {
      setSubjects([]);
      setSubjectsScope('default');
    }
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setLoading(true);
    
    try {
      if (!ApexChartsRef.current) {
        ApexChartsRef.current = await loadApex();
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
      
      if (form.user_id) {
        const paramsOne = new URLSearchParams({
          user_id: form.user_id,
          subject_id: form.subject_id,
          start_jd: String(startJd),
          end_jd: String(endJd)
        }).toString();
        const dataOneRaw = await api.get('/missions/streaks?' + paramsOne);
        const dataOne = extractStreakPayload(dataOneRaw, form.user_id);
        setPayload({ ...dataOne, orderBy: form.order_by });
        clearCharts();
        renderChartForElement(chartsContainerRef.current || chartRef.current, dataOne, form.order_by, null);
      } else {
        const idParams = {};
        if (form.class_id) idParams.class_id = form.class_id;
        else if (form.school_id) idParams.school_id = form.school_id;
        
        const paramsAll = new URLSearchParams({
          ...idParams,
          subject_id: form.subject_id,
          start_jd: String(startJd),
          end_jd: String(endJd)
        }).toString();
        const dataRaw = await api.get('/missions/streaks?' + paramsAll);
        
        const children = getFilteredSoldiers();
        children.sort((a, b) => {
          const an = (a.first + ' ' + a.last).toLowerCase();
          const bn = (b.first + ' ' + b.last).toLowerCase();
          return an < bn ? -1 : an > bn ? 1 : 0;
        });
        
        clearCharts();
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
          if (chartsContainerRef.current) chartsContainerRef.current.appendChild(wrapper);
          renderChartForElement(div, data, form.order_by, child.user_id);
        }
      }
    } catch (err) {
      console.error(err);
    } finally {
      setLoading(false);
    }
  };

  const sortTasks = (tasks, orderBy) => {
    const arr = tasks.slice();
    if (orderBy === 'completed-asc') {
      arr.sort((a, b) => a.jds.length - b.jds.length);
    } else if (orderBy === 'completed-desc') {
      arr.sort((a, b) => b.jds.length - a.jds.length);
    } else if (orderBy === 'alpha') {
      arr.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
    } else {
      const typeRank = { daily: 0, weekly: 1, shabbos: 2 };
      arr.sort((a, b) => {
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
  };

  const buildSeriesFrom = (sortedTasks, payload) => {
    const dataPoints = [];
    const missingPoints = [];
    const counts = {};
    const totalDaysInWindow = (payload.endJd - payload.startJd + 1);
    sortedTasks.forEach(t => {
      const ranges = groupJdRanges(t.jds);
      const raw = groupJdRangesRaw(t.jds);
      const inv = invertRanges(payload.startJd, payload.endJd + 1, raw);
      if (!counts[t.name]) counts[t.name] = { done: 0, total: totalDaysInWindow };
      counts[t.name].done += t.jds.length;
      ranges.forEach(r => { dataPoints.push({ x: t.name, y: r }); });
      inv.forEach(r => {
        const startMs = jdToUtcDate(r[0]).getTime();
        const endMs = jdToUtcDate(r[1]).getTime();
        missingPoints.push({ x: t.name, y: [startMs, endMs] });
      });
    });
    return { dataPoints, missingPoints, counts, sorted: sortedTasks };
  };

  const renderChart = () => {
    if (!payload || !ApexChartsRef.current) return;
    const order = form.order_by || payload.orderBy || 'campaign';
    const sorted = sortTasks(payload.tasks || [], order);
    const built = buildSeriesFrom(sorted, payload);
    chartCountsByNameRef.current = built.counts;

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
          formatter: (val) => {
            const m = chartCountsByNameRef.current[val];
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

    if (chartObjRef.current) {
      chartObjRef.current.updateOptions(options, true, true);
    } else if (chartRef.current && chartRef.current.id === 'chart-container' && ApexChartsRef.current) {
      chartObjRef.current = new ApexChartsRef.current(chartRef.current, options);
      chartObjRef.current.render();
    }
  };

  const renderChartForElement = (targetEl, payload, orderBy, key) => {
    if (!payload || !ApexChartsRef.current || !targetEl) return;
    const sorted = sortTasks(payload.tasks || [], orderBy || 'campaign');
    const built = buildSeriesFrom(sorted, payload);
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
          formatter: (val) => {
            const m = built.counts[val];
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

    const chart = new ApexChartsRef.current(targetEl, options);
    chart.render();
    if (key) chartObjsRef.current[key] = chart;
  };

  const clearCharts = () => {
    if (chartObjRef.current) {
      try { chartObjRef.current.destroy(); } catch(e) {}
      chartObjRef.current = null;
    }
    for (const k in chartObjsRef.current) {
      try { chartObjsRef.current[k].destroy(); } catch(e) {}
      delete chartObjsRef.current[k];
    }
    if (chartsContainerRef.current) chartsContainerRef.current.innerHTML = '';
  };

  const getFilteredSoldiers = () => {
    const soldiersList = soldiers || [];
    const schoolId = form.school_id;
    const classId = form.class_id || false;
    if (!schoolId) return [];
    let s = soldiersList.filter(sol => sol.school_id === String(schoolId) && (!!sol.class_id));
    if (classId) s = s.filter(sol => sol.class_id === String(classId));
    return s;
  };

  return (
    <div id="StreaksPage">
      <Callout title="Tasks Accomplished">
        <p>View accomplished vs missing days for selected missions over a date range.</p>
      </Callout>

      <form onSubmit={handleSubmit}>
        <Row>
          <Col sm="4">
            <label>Base</label>
            <BaseSelect
              required
              value={form.school_id}
              onChange={handleSelectChange('school_id')} />
          </Col>
          <Col sm="4">
            <label>Platoon</label>
            <PlatoonSelect
              isClearable
              value={form.class_id}
              schoolId={form.school_id}
              openMenuOnFocus={false}
              placeholder='All Platoons'
              onChange={handlePlatoonChange} />
          </Col>
          <Col sm="4">
            <label>Soldier</label>
            <SoldierSelect
              key={(form.school_id || '0') + '-' + (form.class_id || '0')}
              isClearable
              registeredOnly
              showAllOption
              onlyReloadSoldiersIfNotLoaded
              value={form.user_id}
              classId={form.class_id}
              schoolId={form.school_id}
              openMenuOnFocus={false}
              placeholder='All Soldiers'
              onChange={handleSelectChange('user_id')} />
          </Col>
        </Row>
        <Row style={{ height: 10 }} />
        <Row>
          <Col sm="4">
            <label>Start</label>
            <input type="date" name="start" value={form.start} onChange={handleChange} className="form-control" />
          </Col>
          <Col sm="4">
            <label>End</label>
            <input type="date" name="end" value={form.end} onChange={handleChange} className="form-control" />
          </Col>
          <Col sm="4">
            <label>Or last Number of days</label>
            <Select
              options={[
                { value: '7', label: '7' },
                { value: '30', label: '30' },
                { value: '60', label: '60' },
                { value: '90', label: '90' },
              ]}
              value={[
                { value: '7', label: '7' },
                { value: '30', label: '30' },
                { value: '60', label: '60' },
                { value: '90', label: '90' },
              ].find(o => String(o.value) === String(form.date_range)) || null}
              onChange={handleSelectChange('date_range')}
            />
          </Col>
        </Row>
        <Row style={{ marginTop: 12 }}>
          <Col sm="6">
            <label>Campaign</label>
            <Select
              key={'scope-' + String(subjectsScope) + '-' + String(form.user_id || form.class_id || 'none') + '-' + String(subjects.length)}
              options={subjects.map(s => ({ value: s.subject_id, label: s.subject_name }))}
              value={subjects.map(s => ({ value: s.subject_id, label: s.subject_name }))
                .find(o => String(o.value) === String(form.subject_id)) || null}
              onChange={handleSelectChange('subject_id')}
              isDisabled={!form.school_id}
              placeholder={form.school_id ? (subjects.length ? 'Select campaign' : 'No campaigns') : 'Select a base first'}
              isClearable={false}
            />
          </Col>
        </Row>

        <Row className="buttons" style={{ marginTop: 15 }}>
          <Col xs="12" className="text-center">
            <Button color="primary" type="submit" disabled={loading}>
              <InlineSync loading={loading} /> Show Tasks
            </Button>
          </Col>
        </Row>
      </form>

      <hr />

      {loading && <Spinner />}

      {(chartObjRef.current || (chartsContainerRef.current && chartsContainerRef.current.childNodes && chartsContainerRef.current.childNodes.length > 0)) && (
        <div style={{ margin: '12px 0' }}>
          <Row>
            <Col sm="6">
              <label>Sort</label>
              <Select
                options={[
                  { value: 'campaign', label: 'Mission sheet order' },
                  { value: 'completed-asc', label: 'Completed (ascending)' },
                  { value: 'completed-desc', label: 'Completed (descending)' },
                  { value: 'alpha', label: 'Task name (A–Z)' },
                ]}
                value={[
                  { value: 'campaign', label: 'Mission sheet order' },
                  { value: 'completed-asc', label: 'Completed (ascending)' },
                  { value: 'completed-desc', label: 'Completed (descending)' },
                  { value: 'alpha', label: 'Task name (A–Z)' },
                ].find(o => o.value === form.order_by) || null}
                onChange={handleSelectChange('order_by')}
              />
            </Col>
          </Row>
        </div>
      )}

      <div className="streaks">
        <div id="chart-container" ref={chartRef} />
        <div id="charts" ref={chartsContainerRef} />
      </div>
    </div>
  );
};

const mapStateToProps = ({ login, base }) => ({
  login: login.current_login,
  soldiers: base?.soldiers?.soldiers
});

export default connect(mapStateToProps, { getSoldiers })(TasksAccomplished);
