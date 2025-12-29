import React, { useState } from 'react';
// components
import { Row, Col, Input, UncontrolledTooltip, FormFeedback } from 'reactstrap';
// functions
import { onInputChange, onSelectChange } from 'functions/events';
import InstitutionSelect from 'components/selects/redux/InstitutionSelect';

export const BaseRow = (props) => {
  const {
    school_name, school_name_he, inst_id, hachayol_name, required, onUpdate
  } = props;

  const [touched, setTouched] = useState({});

  const handleInputChange = onInputChange(onUpdate);
  const handleSelectChange = onSelectChange(onUpdate);
  const onBlur = (field) => () => setTouched(prev => ({ ...prev, [field]: true }));

  // Validation Logic matches patterns in original code
  // pattern='^[a-zA-Z ,]{3,255}$'
  const isSchoolNameInvalid = touched.school_name && school_name && !/^[a-zA-Z ,]{3,255}$/.test(school_name);

  // pattern='^[^a-zA-Z]{3,255}$' (No English letters, 3-255 chars)
  const isSchoolNameHeInvalid = touched.school_name_he && school_name_he && !/^[^a-zA-Z]{3,255}$/.test(school_name_he);

  // pattern='^.{3,65}$'
  const isHachayolNameInvalid = touched.hachayol_name && hachayol_name && !/^.{3,65}$/.test(hachayol_name);

  // props for all inputs
  const inputProps = { required, onChange: handleInputChange };

  return (
    <Row>
      <Col xs={12} sm={6}>
        <label>Base Name (Public)</label>
        <Input {...inputProps} name='school_name'
          value={school_name || ''}
          maxLength={255} title="3 to 255 English letters"
          onBlur={onBlur('school_name')}
          invalid={isSchoolNameInvalid}
        />

        <FormFeedback>Please enter 3 or more <em>English</em> letters</FormFeedback>
      </Col>

      <Col xs={12} sm={6} dir='rtl'>
        <label>Hebrew/Banner Name</label>
        <Input {...inputProps} name='school_name_he'
          maxLength={255} value={school_name_he || ''}
          title="Three or more Hebrew letters"
          onBlur={onBlur('school_name_he')}
          invalid={isSchoolNameHeInvalid}
        />

        <FormFeedback>Please enter 3 or more <em>Hebrew</em> letters</FormFeedback>
      </Col>

      <Col xs={12} sm={6}>
        <label id='inst-label'>Institution Type</label>

        <InstitutionSelect
          required
          value={inst_id}
          onChange={handleSelectChange('inst_id')} />

        <UncontrolledTooltip placement="top" target="inst-label" autohide={false}>
          Connect this base to an institution for them to access data associated with your base.
        </UncontrolledTooltip>
      </Col>

      <Col xs={12} sm={6}>
        <label>School Name to use for Tzivos Hashem Publications</label>
        <Input {...inputProps} name='hachayol_name'
          value={hachayol_name || ''}
          title="3 to 65 letters" maxLength={65}
          onBlur={onBlur('hachayol_name')}
          invalid={isHachayolNameInvalid}
        />

        <FormFeedback>Please enter 3 or more letters</FormFeedback>
      </Col>
    </Row>
  );
};
