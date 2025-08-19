import React, { Component, Fragment } from 'react';
import API from "../../../../../api/api";
// components
import { Input, Row, Col, TabPane, Button } from 'reactstrap';
import { NavigationRow } from '../rows/registration/NavigationRow';
import { Form, Checkbox } from "components/inputs";
import { GradeSelect } from "components/selects";

export class ContactsTab extends Component {

  state = { 
    // Single source of truth for form
    contacts: {
      bc: { id: null, name: '', email: '', phone: '' },
      chidon: { id: null, name: '', email: '', phone: '', is_bc: '0' },
      principal: { id: null, name: '', email: '', phone: '', grades: '' },
      extra_principals: [] // each: { id, name, email, phone, grades }
    },
    saveError: null,
    saving: false,
    baseCommanderIsChidon: false,
    extraPrincipalsError: null,
  }

  componentDidMount() {
    this.fetchContacts();
  }

  fetchContacts = () => {
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
        this.setState({ contacts: normalized });
        this.checkIfBaseCommanderIsChidon({ bc: Array.isArray(response.bc) ? response.bc : [response.bc].filter(Boolean) });
      })
      .catch(error => {
        console.error('Error fetching contacts:', error);
        this.setState({ 
          contacts: {
            bc: { id: null, name: '', email: '', phone: '' },
            chidon: { id: null, name: '', email: '', phone: '', is_bc: '0' },
            principal: { id: null, name: '', email: '', phone: '', grades: '' },
            extra_principals: []
          }
        });
      });
  }

  checkIfBaseCommanderIsChidon = (contacts) => {
    // Check if bc array has someone marked as chidon coordinator
    const hasDoubleRole = contacts.bc && contacts.bc.some(contact => contact.isChidonCoordinator);
    this.setState({ baseCommanderIsChidon: hasDoubleRole });
  }

  toggleBaseCommanderIsChidon = () => {
    const { baseCommanderIsChidon, contacts } = this.state;
    const newValue = !baseCommanderIsChidon;
    
    // Update all Base Commanders with the flag
    const updatedContacts = {
      ...contacts,
      bc: contacts.bc.map(contact => ({
        ...contact,
        isChidonCoordinator: newValue
      }))
    };
    
    this.setState({ 
      baseCommanderIsChidon: newValue,
      contacts: updatedContacts
    });
  }

  setValue = (role, index, key, value) => {
    const { contacts } = this.state;
    const updatedRole = [...contacts[role]];
    updatedRole[index] = {
      ...updatedRole[index],
      [key]: value
    };
    
    this.setState({
      contacts: {
        ...contacts,
        [role]: updatedRole
      }
    });
  }

  save = (e) => {
    e.preventDefault();
    
    // Clear any previous errors and set saving state
    this.setState({ saveError: null, saving: true });
    
    // Prepare the data in the format the API expects
    const dataToSave = { ...this.state.contacts };
    // Include school id for updates as well
    dataToSave.school_id = this.props.base && (this.props.base.id || this.props.base.school_id);
    
    // Also save the baseCommanderIsChidon setting if needed
    if (this.props.onUpdate) {
      this.props.onUpdate({ baseCommanderIsChidon: this.state.baseCommanderIsChidon });
    }
    
    // Send the update to the API
    API.patch('/core/school_contacts', dataToSave)
      .then(() => {
        this.setState({ saving: false });
        this.props.onSubmit(e);
      })
      .catch(error => {
        console.error('Error saving contacts:', error);
        let errorMessage = 'An error occurred while saving contacts. Please try again.';
        
        if (error.response && error.response.data && error.response.data.message) {
          errorMessage = error.response.data.message;
        } else if (error.message) {
          errorMessage = error.message;
        }
        
        this.setState({ 
          saveError: errorMessage,
          saving: false 
        });
        // Scroll to top to show error
        const tabPane = document.querySelector('#ContactsTab');
        if (tabPane) {
          tabPane.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
  }

  // Simple setters for simplified shape
  setField = (section, field, value, index = null) => {
    this.setState(prev => {
      const updated = { ...prev.contacts };
      if (section === 'extra_principals') {
        const list = [...updated.extra_principals];
        list[index] = { ...list[index], [field]: value };
        updated.extra_principals = list;
      } else {
        updated[section] = { ...updated[section], [field]: value };
      }
      return { contacts: updated };
    });
  }

  // Helper function to convert comma-delimited string to array for multiselect
  gradesStringToArray = (gradesString) => {
    if (!gradesString) return [];
    return gradesString.split(',').map(grade => grade.trim()).filter(grade => grade);
  }

  // Helper function to convert array back to comma-delimited string for database
  gradesArrayToString = (gradesArray) => {
    if (!gradesArray || !Array.isArray(gradesArray)) return '';
    return gradesArray.join(', ');
  }

  // Handler for grade multiselect changes
  onGradesChange = (section, index = null) => (selectedOptions) => {
    const gradesArray = selectedOptions ? selectedOptions.map(option => option.value) : [];
    const gradesString = this.gradesArrayToString(gradesArray);
    this.setField(section, 'grades', gradesString, index);
  }

  addExtraPrincipal = () => {
    this.setState(prev => ({
      contacts: {
        ...prev.contacts,
        extra_principals: [...prev.contacts.extra_principals, { name: '', email: '', phone: '', grades: '' }]
      }
    }));
  }

  removeExtraPrincipal = (index) => {
    this.setState(prev => ({
      contacts: {
        ...prev.contacts,
        extra_principals: prev.contacts.extra_principals.filter((_, i) => i !== index)
      }
    }));
  }

  saveNewExtraPrincipals = () => {
    const { contacts } = this.state;
    const newPrincipals = (contacts.extra_principals || []).filter(p => !p.id);
    // reset section error
    this.setState({ extraPrincipalsError: null });
    if (newPrincipals.length === 0) {
      this.setState({ extraPrincipalsError: 'There are no new additional principals to save.' });
      return;
    }
    // basic validation: require name/email/phone
    const invalid = newPrincipals.some(p => !p.name || !p.email || !p.phone);
    if (invalid) {
      this.setState({ extraPrincipalsError: 'Please fill in Name, Email and Phone for new additional principals.' });
      return;
    }
    this.setState({ saving: true, saveError: null });
    const school_id = this.props.base && (this.props.base.id || this.props.base.school_id);
    const payload = {
      school_id,
      extra_principals: newPrincipals.map(p => ({ name: p.name, email: p.email, phone: p.phone, grades: p.grades }))
    };
    API.post('/core/school_contacts', payload)
      .then(() => {
        this.fetchContacts();
        this.setState({ saving: false, extraPrincipalsError: null });
      })
      .catch(error => {
        const errorMessage = (error && error.message) || 'Failed to add new additional principals.';
        this.setState({ extraPrincipalsError: errorMessage, saving: false });
      });
  }

  // remove extra principal is intentionally not supported in this UI

  renderContactSection = (title, role, color) => {
    const { baseCommanderIsChidon, contacts } = this.state;
    const borderColor = color || '#007bff';
    const contactsList = contacts[role] || [];
    
    // Don't show Chidon Coordinator inputs if Base Commander is also Chidon
    const showInputs = !(role === 'chidon' && baseCommanderIsChidon);

    return (
      <div className="contact-section mb-4">
        <h5 className="text-primary mb-3" style={{ 
          borderBottom: '2px solid ' + borderColor, 
          paddingBottom: '8px'
        }}>
          {title}
        </h5>
        
        {role === 'chidon' && (
          <div className="mb-3">
            <Checkbox
              name="baseCommanderIsChidon"
              checked={baseCommanderIsChidon}
              onChange={this.toggleBaseCommanderIsChidon}
            >
              Base Commander is also the Chidon Coordinator
            </Checkbox>
          </div>
        )}
        
        {showInputs && (
          contactsList.length > 0 ? (
            contactsList.map((contact, index) => (
              <Row key={index} className="mb-3 align-items-end">
                <Col sm={4}>
                  <label>Name</label>
                  <Input 
                    required 
                    placeholder="Full Name"
                    defaultValue={contact.name} 
                    onChange={e => this.setValue(role, index, 'name', e.target.value)} 
                  />
                </Col>
                <Col sm={4}>
                  <label>Email</label>
                  <Input 
                    required 
                    type='email'
                    placeholder="email@example.com"
                    defaultValue={contact.email} 
                    onChange={e => this.setValue(role, index, 'email', e.target.value)}
                    pattern='^[^\s@]+@[^\s@]+\.[^\s@]+$'
                  />
                </Col>
                <Col sm={3}>
                  <label>Phone</label>
                  <Input 
                    type='tel'
                    placeholder="(555) 123-4567"
                    defaultValue={contact.phone} 
                    onChange={e => this.setValue(role, index, 'phone', e.target.value)} 
                  />
                </Col>
              </Row>
            ))
          ) : (
            <p className="text-muted ml-3">No {title.toLowerCase()} assigned yet.</p>
          )
        )}
      </div>
    );
  }

  render() {
    const { tabId, back, onValidChange } = this.props;
    const { saveError, saving } = this.state;

    return (
      <TabPane tabId={tabId}>
        <Form
          validateAfterSubmit
          onSubmit={this.save}
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
                <label>Name</label>
                <Input value={this.state.contacts.bc.name} onChange={e => this.setField('bc', 'name', e.target.value)} />
              </Col>
              <Col sm={4} className="mb-3">
                <label>Email</label>
                <Input type="email" value={this.state.contacts.bc.email} onChange={e => this.setField('bc', 'email', e.target.value)} />
              </Col>
              <Col sm={4} className="mb-3">
                <label>Phone</label>
                <Input value={this.state.contacts.bc.phone} onChange={e => this.setField('bc', 'phone', e.target.value)} />
              </Col>
            </Row>

            {/* Chidon Contact */}
            <h5 className="mt-3">Chidon Contact</h5>
            <Row>
              {this.state.contacts.chidon.is_bc !== '1' && (
                <Fragment>
                  <Col sm={4} className="mb-3">
                    <label>Name</label>
                    <Input value={this.state.contacts.chidon.name} onChange={e => this.setField('chidon', 'name', e.target.value)} />
                  </Col>
                  <Col sm={4} className="mb-3">
                    <label>Email</label>
                    <Input type="email" value={this.state.contacts.chidon.email} onChange={e => this.setField('chidon', 'email', e.target.value)} />
                  </Col>
                  <Col sm={4} className="mb-3">
                    <label>Phone</label>
                    <Input value={this.state.contacts.chidon.phone} onChange={e => this.setField('chidon', 'phone', e.target.value)} />
                  </Col>
                </Fragment>
              )}
              <Col sm={12} className="mb-3">
                <Checkbox
                  name="chidon_is_bc"
                  checked={this.state.contacts.chidon.is_bc === '1'}
                  onChange={() => this.setField('chidon', 'is_bc', this.state.contacts.chidon.is_bc === '1' ? '0' : '1')}
                >
                  Chidon Coordinator is the Base Commander
                </Checkbox>
              </Col>
            </Row>

            {/* Principal */}
            <h5 className="mt-3">Principal</h5>
            <Row>
              <Col sm={4} className="mb-3">
                <label>Name</label>
                <Input value={this.state.contacts.principal.name} onChange={e => this.setField('principal', 'name', e.target.value)} />
              </Col>
              <Col sm={4} className="mb-3">
                <label>Email</label>
                <Input type="email" value={this.state.contacts.principal.email} onChange={e => this.setField('principal', 'email', e.target.value)} />
              </Col>
              <Col sm={4} className="mb-3">
                <label>Phone</label>
                <Input value={this.state.contacts.principal.phone} onChange={e => this.setField('principal', 'phone', e.target.value)} />
              </Col>
              <Col sm={12} className="mb-3">
                <label>Grades (Leave blank for all)</label>
                <GradeSelect
                  isMulti
                  isClearable
                  values={this.gradesStringToArray(this.state.contacts.principal.grades)}
                  openMenuOnFocus={false}
                  placeholder="Select Grades"
                  onChange={this.onGradesChange('principal')}
                />
              </Col>
            </Row>

            {/* Extra Principals */}
            <div className="d-flex justify-content-between align-items-center mt-3 mb-2">
              <h5 className="mb-0">Additional Principals</h5>
              <Button color="outline-primary" size="sm" onClick={this.addExtraPrincipal}>Add</Button>
            </div>
            {this.state.contacts.extra_principals.length === 0 && (
              <div className="text-muted">No additional principals</div>
            )}
            {this.state.contacts.extra_principals.map((p, idx) => (
              <div key={idx} className="border rounded p-3 mb-3">
                <div className="d-flex justify-content-between align-items-center mb-2">
                  <h6 className="mb-0">Additional Principal {idx + 1}</h6>
                  {!p.id && (
                    <Button 
                      color="danger" 
                      size="sm" 
                      onClick={() => this.removeExtraPrincipal(idx)}
                      className="btn-sm"
                    >
                      <i className="fa fa-trash"></i> Remove
                    </Button>
                  )}
                </div>
                <Row>
                  <Col sm={4} className="mb-3">
                    <label>Name</label>
                    <Input value={p.name} onChange={e => this.setField('extra_principals', 'name', e.target.value, idx)} />
                  </Col>
                  <Col sm={4} className="mb-3">
                    <label>Email</label>
                    <Input type="email" value={p.email} onChange={e => this.setField('extra_principals', 'email', e.target.value, idx)} />
                  </Col>
                  <Col sm={3} className="mb-3">
                    <label>Phone</label>
                    <Input value={p.phone} onChange={e => this.setField('extra_principals', 'phone', e.target.value, idx)} />
                  </Col>
                  
                  <Col sm={12} className="mb-3">
                    <label>Grades (Leave blank for all)</label>
                    <GradeSelect
                      isMulti
                      isClearable
                      values={this.gradesStringToArray(p.grades)}
                      openMenuOnFocus={false}
                      placeholder="Select Grades"
                      onChange={this.onGradesChange('extra_principals', idx)}
                    />
                  </Col>
                </Row>
              </div>
            ))}

            {this.state.extraPrincipalsError && (
              <div className="alert alert-danger py-1">{this.state.extraPrincipalsError}</div>
            )}

            {/* Save all new additional principals */}
            {this.state.contacts.extra_principals.some(p => !p.id) && (
              <div className="text-end">
                <Button
                  type="button"
                  color="primary"
                  size="sm"
                  onClick={this.saveNewExtraPrincipals}
                  disabled={this.state.saving}
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
}