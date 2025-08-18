// RegistrationInfo component (scoped and exposed via window.AppComponents)
(function() {
  window.AppComponents = window.AppComponents || {};

  const { Card, Button, Table, Form, Alert } = ReactBootstrap;
  const { useEffect, useMemo, useState } = React;

  const fieldMeta = {
    year: { editable: false, type: 'text' },
    school_number: { editable: false, type: 'text' },
    school_name: { editable: false, type: 'text' },
    reg_type: { editable: true, type: 'select', options: [
      { value: '0', label: 'N/A', disabled: true },
      { value: '1', label: 'In Tuition' },
      { value: '2', label: 'Guaranteed' },
      { value: '3', label: 'By Parent' }
    ] },
    school_charge_date: { editable: true, type: 'date' },
    chayolei_fee: { editable: true, type: 'number', step: '0.01' },
    chidon_fee: { editable: true, type: 'number', step: '0.01' },
    balance: { editable: true, type: 'number', step: '0.01' },
    child_fee: { editable: true, type: 'number', step: '0.01' },
    early_bird: { editable: true, type: 'date' },
    registration_notes: { editable: true, type: 'textarea' }
  };

  function safeGet(obj, key, fallback = '') {
    return (obj && Object.prototype.hasOwnProperty.call(obj, key)) ? (obj[key] ?? fallback) : fallback;
  }

  async function fetchBaseSettings(schoolId) {
    // Attempt to GET settings; if unsupported, return minimal defaults
    try {
      const res = await fetch(`/api/core/bases?id=${encodeURIComponent(schoolId)}`, { method: 'GET' });
      if (!res.ok) throw new Error('GET not supported');
      const data = await res.json();
      return data || {};
    } catch (_) {
      return {};
    }
  }

  async function updateBaseSettings(schoolId, updates) {
    const res = await fetch(`/api/core/bases?id=${encodeURIComponent(schoolId)}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(updates)
    });
    return res.json();
  }

  window.AppComponents.RegistrationInfo = function RegistrationInfo({ year, schools, selectedValues, settingsOptions }) {
    const [rows, setRows] = useState([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState('');
    const [info, setInfo] = useState('');

    const selectedIds = useMemo(() => {
      if (!selectedValues || selectedValues.length === 0) return [];
      const includesAll = selectedValues.some(s => String(s.value) === '0');
      if (includesAll) return (schools || []).map(s => String(s.id));
      return selectedValues.map(s => String(s.value));
    }, [selectedValues, schools]);

    const schoolMap = useMemo(() => {
      const map = new Map();
      (schools || []).forEach(s => map.set(String(s.id), s));
      return map;
    }, [schools]);

    const headers = useMemo(() => Object.keys(settingsOptions || {}), [settingsOptions]);

    useEffect(() => {
      let cancelled = false;
      (async () => {
        try {
          setLoading(true);
          const initialRows = [];
          for (const id of selectedIds) {
            const base = schoolMap.get(String(id));
            if (!base) continue;
            const current = await fetchBaseSettings(id);
            initialRows.push({
              school_id: id,
              year: String(year || ''),
              school_number: safeGet(base, 'school_number', ''),
              school_name: safeGet(base, 'name', ''),
              reg_type: safeGet(current, 'reg_type', '0'),
              school_charge_date: safeGet(current, 'school_charge_date', ''),
              chayolei_fee: safeGet(current, 'chayolei_fee', ''),
              chidon_fee: safeGet(current, 'chidon_fee', ''),
              balance: safeGet(current, 'balance', ''),
              child_fee: safeGet(current, 'child_fee', ''),
              early_bird: safeGet(current, 'early_bird', ''),
              registration_notes: safeGet(current, 'registration_notes', ''),
              _dirty: false,
              _saving: false
            });
          }
          if (!cancelled) setRows(initialRows);
        } catch (e) {
          if (!cancelled) setError('Failed to load registration settings');
        } finally {
          if (!cancelled) setLoading(false);
        }
      })();
      return () => { cancelled = true; };
    }, [selectedIds, year, schoolMap]);

    const setCell = (idx, key, value) => {
      setRows(prev => prev.map((r, i) => i === idx ? { ...r, [key]: value, _dirty: true } : r));
    };

    const onSaveRow = async (idx) => {
      setInfo(''); setError('');
      setRows(prev => prev.map((r, i) => i === idx ? { ...r, _saving: true } : r));
      const row = rows[idx];
      const updates = {};
      headers.forEach(h => {
        if (!fieldMeta[h] || !fieldMeta[h].editable) return;
        let v = row[h];
        if (h === 'child_fee' && (v === '' || v === null)) v = null;
        updates[h] = v;
      });
      // registration_notes may be separate id in DOM on legacy page; here included in updates
      try {
        const res = await updateBaseSettings(row.school_id, updates);
        if (res && res.success) {
          setInfo('Base Updated');
          setRows(prev => prev.map((r, i) => i === idx ? { ...r, _dirty: false, _saving: false } : r));
        } else {
          throw new Error(res && res.message ? res.message : 'Update failed');
        }
      } catch (e) {
        setError(e.message || 'Update failed');
        setRows(prev => prev.map((r, i) => i === idx ? { ...r, _saving: false } : r));
      }
    };

    const onDeactivate = async (idx) => {
      setInfo(''); setError('');
      const row = rows[idx];
      try {
        const res = await updateBaseSettings(row.school_id, { school_era: row.year });
        if (res && res.success) {
          setInfo('Base Deactivated');
        } else {
          throw new Error(res && res.message ? res.message : 'Deactivate failed');
        }
      } catch (e) {
        setError(e.message || 'Deactivate failed');
      }
    };

    const renderCell = (row, idx, key) => {
      const meta = fieldMeta[key] || { editable: false, type: 'text' };
      const value = safeGet(row, key, '');
      if (!meta.editable) return (<td>{value}</td>);
      switch (meta.type) {
        case 'select':
          return (
            <td>
              <Form.Select value={value} onChange={(e) => setCell(idx, key, e.target.value)}>
                {meta.options.map(opt => (
                  <option key={opt.value} value={opt.value} disabled={opt.disabled}>{opt.label}</option>
                ))}
              </Form.Select>
            </td>
          );
        case 'date':
          return (
            <td>
              <Form.Control type="date" value={value || ''} onChange={(e) => setCell(idx, key, e.target.value)} />
            </td>
          );
        case 'number':
          return (
            <td>
              <Form.Control type="number" step={meta.step || '1'} value={value} onChange={(e) => setCell(idx, key, e.target.value)} />
            </td>
          );
        case 'textarea':
          return (
            <td>
              <Form.Control as="textarea" rows={3} value={value} onChange={(e) => setCell(idx, key, e.target.value)} />
            </td>
          );
        default:
          return (
            <td>
              <Form.Control value={value} onChange={(e) => setCell(idx, key, e.target.value)} />
            </td>
          );
      }
    };

    return (
      <Card className="mb-4">
        <Card.Header>
          <h6 className="mb-0">School Registration Settings</h6>
        </Card.Header>
        <Card.Body>
          {error && <Alert variant="danger" className="mb-3">{error}</Alert>}
          {info && <Alert variant="success" className="mb-3">{info}</Alert>}
          <div className="table-responsive">
            <Table striped bordered hover>
              <thead className="table-dark">
                <tr>
                  {headers.map(h => (
                    <th key={h}>{settingsOptions[h]}</th>
                  ))}
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                {loading ? (
                  <tr><td colSpan={headers.length + 1}>Loading...</td></tr>
                ) : (
                  rows.map((row, idx) => (
                    <tr key={row.school_id}>
                      {headers.map(h => renderCell(row, idx, h))}
                      <td className="text-center">
                        <Button size="sm" variant="primary" className="me-2" disabled={!row._dirty || row._saving} onClick={() => onSaveRow(idx)}>
                          {row._saving ? 'Saving...' : 'Save Changes'}
                        </Button>
                        <Button size="sm" variant="danger" onClick={() => onDeactivate(idx)}>Deactivate</Button>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </Table>
          </div>
        </Card.Body>
      </Card>
    );
  };

})();


