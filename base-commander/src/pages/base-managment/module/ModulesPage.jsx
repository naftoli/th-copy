import React, { useState, useEffect } from 'react';
import { connect } from 'react-redux';
// components
import { Callout, InlineSync } from 'components/ui';
import { Row, Col } from 'reactstrap';
import { PlatoonSelect, SoldierSelect, BaseSelect } from 'components/inputs';
import ModuleControlRow from './ModuleControlRow'
// functions
import { setTitle } from 'functions/utils';
import { isAdmin, isBC } from 'functions/login';
// style
import './ModulesPage.scss';

const modules = [
  { moduleKey: "hachayols", title: "Hachayols" },
  { moduleKey: "medals_ranks", title: "Physical Medals / Rankbooks" }
]

function ModulesPage (props) {
  const { login, laoding } = props;
  const [ selections, setSelections ] = useState({school_id: false, class_ids: [], user_ids: []})
  const { school_id, class_ids, user_ids } = selections;
  const selectionScope = user_ids && user_ids.length ? "user"
    : class_ids && class_ids.length ? "class"
    : school_id ? "school" : null

  const selectionIds = user_ids && user_ids.length ? user_ids
    : class_ids && class_ids.length ? class_ids
    : school_id ? [school_id] : null

  // componentDidMount
  useEffect(() => {
    setTitle('Bulk Soldiers Editer');
    // set the default school id and class id
    const { school_id, class_id } = login;
    setSelections(() => ( { school_id, class_ids: class_id ? [class_id] : []} ));
  }, [])

  const schoolChange = ({ value }) => {
    setSelections(() => ({ school_id: parseInt(value, 10), class_ids: [], user_ids: []}))
  };
  const multiSelectChange = key => values => {
    setSelections(prevState => ({ ...prevState, [key]: values.map(val => val.value) }))
  };


  return (
    <div id='ModulesPage'>
      <Callout title='Modules'>
        <p>
          Turn modules on or off for a base, plattons, or solddiers.
        </p>
      </Callout>

      <Row>
        <Col sm={6}>
          <label>Base</label>
          <BaseSelect
            name='school_id' onChange={schoolChange}
            value={school_id} isDisabled={!isAdmin(login.code)} />
          <input type='hidden' value={school_id} name='school_id' />
        </Col>

        <Col sm={6}>
          <label>Platoon(s)</label>
          <PlatoonSelect
            placeholder='All Platoons' isMulti values={class_ids}
            isDisabled={!isBC(login.code)} openMenuOnFocus={false}
            schoolId={school_id} onChange={multiSelectChange('class_ids')} />
          <input type='hidden' value={class_ids} name='class_ids' />
        </Col>

        <Col sm={6}>
          <label>Soldier(s)</label>
          <SoldierSelect
            values={user_ids} isMulti registeredOnly
            schoolId={school_id} classIds={class_ids} openMenuOnFocus={false}
            onChange={multiSelectChange('user_ids')} placeholder='All Soldiers' />
          <input type='hidden' value={user_ids} name='user_ids' />
        </Col>
      </Row>

      <p style={{ marginTop: "1rem", marginBottom: "1rem", textAlign: "center"}}>
        {
          laoding ? <InlineSync loading/>
          : !school_id ? "Nothing selected"
          : user_ids.length ? user_ids.length + " soldier(s) selected."
          : class_ids.length ? class_ids.length + " platoon(s) selected."
          : "Base selected."
        }
      </p>
      <hr />

      {modules.map(module => 
        <ModuleControlRow
          key={module.setting}
          {...module}
          selectionScope={selectionScope}
          selectionIds={selectionIds}
          selections={selections}
          />
      )}

    </div>
  );
}

const mapStateToProps = ({ login, base }) => ({
  login: login.current_login,
  laoding: base.bases.loading || base.platoons.loading || base.soldiers.loading
});

const mapDispatchToProps = () => ({
  // getSoldiers
});

export default connect(mapStateToProps, mapDispatchToProps)(ModulesPage);
