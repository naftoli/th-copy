import React, { useState, useEffect, Fragment } from 'react';
import API from "../../../../../api/api";
// components
import { Input, Row, Col, TabPane, Button } from 'reactstrap';
import { NavigationRow } from '../rows/registration/NavigationRow';
import { Form, Checkbox } from "components/inputs";
import { GradeSelect } from "components/selects";

export const ContactsTab = ({
  tabId,
  back,
  onValidChange,
  base,
  onUpdate,
  onSubmit
}) => {
  const [contacts, setContacts] = useState({
    bc: { id: null, name: '', email: '', phone: '' },
    chidon: { id: null, name: '', email: '', phone: '', is_bc: '0' },
    principal: { id: null, name: '', email: '', phone: '', grades: '' },
    extra_principals: [] // each: { id, name, email, phone, grades }
  });
  const [saveError, setSaveError] = useState(null);
  const [saving, setSaving] = useState(false);
  const [baseCommanderIsChidon, setBaseCommanderIsChidon] = useState(false);
  const [extraPrincipalsError, setExtraPrincipalsError] = useState(null);

  useEffect(() => {
    fetchContacts();
  }, []);

  const fetchContacts = () => {
    API.get('/core/school_contacts')
      .then(response => {
        const ensure = (obj) => ({
          id: obj && obj.id ? obj.id : null,
          name: obj && obj.name ? obj.name : '',
          email: obj && obj.email ? obj.email : '',
          phone: obj && obj.phone ? obj.phone : ''
        });
        const ensureWithGrades = (obj) => ({
          ...ensure(obj),
          grades: Array.isArray(obj && obj.grades) ? obj.grades.join(', ') : (obj && obj.grades) || ''
        });
        const chidonFirst = Array.isArray(response.chidon) ? response.chidon[0] : response.chidon;
        const normalized = {
          bc: ensure(Array.isArray(response.bc) ? response.bc[0] : response.bc),
          chidon: {
            ...ensure(chidonFirst),
            is_bc: chidonFirst && (chidonFirst.is_bc === 1 || chidonFirst.is_bc === '1') ? '1' : '0'
          },
          principal: ensureWithGrades(Array.isArray(response.principal) ? response.principal[0] : response.principal),
          extra_principals: Array.isArray(response.extra_principals)
            ? response.extra_principals.map(p => ensureWithGrades(p))
            : []
        };
        setContacts(normalized);
        checkIfBaseCommanderIsChidon({ bc: Array.isArray(response.bc) ? response.bc : [response.bc].filter(Boolean) });
      })
      .catch(error => {
        console.error('Error fetching contacts:', error);
        setContacts({
          bc: { id: null, name: '', email: '', phone: '' },
          chidon: { id: null, name: '', email: '', phone: '', is_bc: '0' },
          principal: { id: null, name: '', email: '', phone: '', grades: '' },
          extra_principals: []
        });
      });
  }

  const checkIfBaseCommanderIsChidon = (contactsData) => {
    // Check if bc array has someone marked as chidon coordinator
    const hasDoubleRole = contactsData.bc && contactsData.bc.some(contact => contact.isChidonCoordinator);
    setBaseCommanderIsChidon(hasDoubleRole);
  }

  const toggleBaseCommanderIsChidon = () => {
    const newValue = !baseCommanderIsChidon;

    // Update all Base Commanders with the flag
    const updatedContacts = {
      ...contacts,
      bc: contacts.bc.map(contact => ({
        ...contact,
        isChidonCoordinator: newValue
      }))
    };

    setBaseCommanderIsChidon(newValue);
    setContacts(updatedContacts);
  }

  const setValue = (role, index, key, value) => {
    const updatedRole = [...contacts[role]];
    updatedRole[index] = {
      ...updatedRole[index],
      [key]: value
    };

    setContacts(prev => ({
      ...prev,
      [role]: updatedRole
    }));
  }

  const save = (e) => {
    e.preventDefault();

    // Clear any previous errors and set saving state
    setSaveError(null);
    setSaving(true);

    // Prepare the data in the format the API expects
    const dataToSave = { ...contacts };
    // Include school id for updates as well
    dataToSave.school_id = base && (base.id || base.school_id);

    // Also save the baseCommanderIsChidon setting if needed
    if (onUpdate) {
      onUpdate({ baseCommanderIsChidon: baseCommanderIsChidon });
    }

    // Send the update to the API
    API.patch('/core/school_contacts', dataToSave)
      .then(() => {
        setSaving(false);
        onSubmit(e);
      })
      .catch(error => {
        console.error('Error saving contacts:', error);
        let errorMessage = 'An error occurred while saving contacts. Please try again.';

        if (error.response && error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message;
        } else if (error.message) {
          errorMessage = error.message;
        }

        setSaveError(errorMessage);
        setSaving(false);
        // Scroll to top to show error
        const tabPane = document.querySelector('#ContactsTab');
        if (tabPane) {
          tabPane.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
  }

  // Simple setters for simplified shape
  const setField = (section, field, value, index = null) => {
    setContacts(prev => {
      const updated = { ...prev };
      if (section === 'extra_principals') {
        const list = [...updated.extra_principals];
        list[index] = { ...list[index], [field]: value };
        updated.extra_principals = list;
      } else {
        updated[section] = { ...updated[section], [field]: value };
      }
      return updated;
    });
  }

  // Helper function to convert comma-delimited string to array for multiselect
  const gradesStringToArray = (gradesString) => {
    if (!gradesString) return [];
    return gradesString.split(',').map(grade => grade.trim()).filter(grade => grade);
  }

  // Helper function to convert array back to comma-delimited string for database
  const gradesArrayToString = (gradesArray) => {
    if (!gradesArray || !Array.isArray(gradesArray)) return '';
    return gradesArray.join(', ');
  }

  // Handler for grade multiselect changes
  const onGradesChange = (section, index = null) => (selectedOptions) => {
    const gradesArray = selectedOptions ? selectedOptions.map(option => option.value) : [];
    const gradesString = gradesArrayToString(gradesArray);
    setField(section, 'grades', gradesString, index);
  }

  const addExtraPrincipal = () => {
    setContacts(prev => ({
      ...prev,
      extra_principals: [...prev.extra_principals, { name: '', email: '', phone: '', grades: '' }]
    }));
  }

  const removeExtraPrincipal = (index) => {
    setContacts(prev => ({
      ...prev,
      extra_principals: prev.extra_principals.filter((_, i) => i !== index)
    }));
  }

  const saveNewExtraPrincipals = () => {
    const newPrincipals = (contacts.extra_principals || []).filter(p => !p.id);
    // reset section error
    setExtraPrincipalsError(null);
    if (newPrincipals.length === 0) {
      setExtraPrincipalsError('There are no new additional principals to save.');
      return;
    }
    // basic validation: require name/email/phone
    const invalid = newPrincipals.some(p => !p.name || !p.email || !p.phone);
    if (invalid) {
      setExtraPrincipalsError('Please fill in Name, Email and Phone for new additional principals.');
      return;
    }
    setSaving(true);
    setSaveError(null);
    const school_id = base && (base.id || base.school_id);
    const payload = {
      school_id,
      extra_principals: newPrincipals.map(p => ({ name: p.name, email: p.email, phone: p.phone, grades: p.grades }))
    };
    API.post('/core/school_contacts', payload)
      .then(() => {
        fetchContacts();
        setSaving(false);
        setExtraPrincipalsError(null);
      })
      .catch(error => {
        const errorMessage = (error && error.message) || 'Failed to add new additional principals.';
        setExtraPrincipalsError(errorMessage);
        setSaving(false);
      });
  }

  return (
    <TabPane tabId={tabId}>
      <Form
        validateAfterSubmit
        onSubmit={save}
        onValidChange={onValidChange}>

        <div id="ContactsTab">
          <p className='title'>School Contacts</p>

          {saveError && (
            <div className="alert alert-danger">{saveError}</div>
          )}

          {/* Base Commander */}
          <h5 className="mt-3">Base Commander</h5>
          <Row>
            <Col sm={4} className="mb-3">
              <label>Name *</label>
              <Input required value={contacts.bc.name} onChange={e => setField('bc', 'name', e.target.value)} />
            </Col>
            <Col sm={4} className="mb-3">
              <label>Email *</label>
              <Input required type="email" value={contacts.bc.email} onChange={e => setField('bc', 'email', e.target.value)} />
            </Col>
            <Col sm={4} className="mb-3">
              <label>Phone *</label>
              <Input required value={contacts.bc.phone} onChange={e => setField('bc', 'phone', e.target.value)} />
            </Col>
          </Row>
          <Row>
            <Col sm={12} className="mb-3">
              <Checkbox
                name="chidon_is_bc"
                checked={contacts.chidon.is_bc === '1'}
                onChange={() => setField('chidon', 'is_bc', contacts.chidon.is_bc === '1' ? '0' : '1')}
              >
                Chidon Coordinator is the Base Commander
              </Checkbox>
            </Col>
          </Row>

          {/* Chidon Contact */}
          <h5 className="mt-3">Chidon Contact</h5>
          <Row>
            {contacts.chidon.is_bc !== '1' && (
              <Fragment>
                <Col sm={4} className="mb-3">
                  <label>Name *</label>
                  <Input required={contacts.chidon.is_bc === '0'} value={contacts.chidon.name} onChange={e => setField('chidon', 'name', e.target.value)} />
                </Col>
                <Col sm={4} className="mb-3">
                  <label>Email *</label>
                  <Input required={contacts.chidon.is_bc === '0'} type="email" value={contacts.chidon.email} onChange={e => setField('chidon', 'email', e.target.value)} />
                </Col>
                <Col sm={4} className="mb-3">
                  <label>Phone *</label>
                  <Input required={contacts.chidon.is_bc === '0'} value={contacts.chidon.phone} onChange={e => setField('chidon', 'phone', e.target.value)} />
                </Col>
              </Fragment>
            )}
          </Row>

          {/* Principal */}
          <h5 className="mt-3">Principal</h5>
          <Row>
            <Col sm={4} className="mb-3">
              <label>Name *</label>
              <Input required value={contacts.principal.name} onChange={e => setField('principal', 'name', e.target.value)} />
            </Col>
            <Col sm={4} className="mb-3">
              <label>Email *</label>
              <Input required type="email" value={contacts.principal.email} onChange={e => setField('principal', 'email', e.target.value)} />
            </Col>
            <Col sm={4} className="mb-3">
              <label>Phone *</label>
              <Input required value={contacts.principal.phone} onChange={e => setField('principal', 'phone', e.target.value)} />
            </Col>
            <Col sm={12} className="mb-3">
              <label>Grades (Leave blank for all)</label>
              <GradeSelect
                isMulti
                isClearable
                values={gradesStringToArray(contacts.principal.grades)}
                openMenuOnFocus={false}
                placeholder="Select Grades"
                onChange={onGradesChange('principal')}
              />
            </Col>
          </Row>

          {/* Extra Principals */}
          <div className="d-flex justify-content-between align-items-center mt-3 mb-2">
            <h5 className="mb-0">Additional Principals</h5>
            <Button color="outline-primary" size="sm" onClick={addExtraPrincipal}>Add</Button>
          </div>
          {contacts.extra_principals.length === 0 && (
            <div className="text-muted">No additional principals</div>
          )}
          {contacts.extra_principals.map((p, idx) => (
            <div key={idx} className="border rounded p-3 mb-3">
              <div className="d-flex justify-content-between align-items-center mb-2">
                <h6 className="mb-0">Additional Principal {idx + 1}</h6>
                {!p.id && (
                  <Button
                    color="danger"
                    size="sm"
                    onClick={() => removeExtraPrincipal(idx)}
                    className="btn-sm"
                  >
                    <i className="fa fa-trash"></i> Remove
                  </Button>
                )}
              </div>
              <Row>
                <Col sm={4} className="mb-3">
                  <label>Name</label>
                  <Input value={p.name} onChange={e => setField('extra_principals', 'name', e.target.value, idx)} />
                </Col>
                <Col sm={4} className="mb-3">
                  <label>Email</label>
                  <Input type="email" value={p.email} onChange={e => setField('extra_principals', 'email', e.target.value, idx)} />
                </Col>
                <Col sm={3} className="mb-3">
                  <label>Phone</label>
                  <Input value={p.phone} onChange={e => setField('extra_principals', 'phone', e.target.value, idx)} />
                </Col>

                <Col sm={12} className="mb-3">
                  <label>Grades (Leave blank for all)</label>
                  <GradeSelect
                    isMulti
                    isClearable
                    values={gradesStringToArray(p.grades)}
                    openMenuOnFocus={false}
                    placeholder="Select Grades"
                    onChange={onGradesChange('extra_principals', idx)}
                  />
                </Col>
              </Row>
            </div>
          ))}

          {extraPrincipalsError && (
            <div className="alert alert-danger py-1">{extraPrincipalsError}</div>
          )}

          {/* Save all new additional principals */}
          {contacts.extra_principals.some(p => !p.id) && (
            <div className="text-end">
              <Button
                type="button"
                color="primary"
                size="sm"
                onClick={saveNewExtraPrincipals}
                disabled={saving}
              >
                Save New Principals
              </Button>
            </div>
          )}

          <NavigationRow back={back} next disabled={saving} />
        </div>
      </Form>
    </TabPane>
  );
}