import React, { Fragment, useEffect } from 'react';
import { connect } from 'react-redux';
// components
import { Row, Col, Button, ButtonGroup } from 'reactstrap';
import { InlineSync } from 'components/ui';
// state
import { getModule, updateModule } from 'store/base/modules/operations';


function ModuleControlRow ({
  moduleKey, title, description, loading, updating, updatingValue, selections,
  canEnable, canDisable,
  getModule, updateModule
}) {

  const handleToggle = (value) => {
    const {user_ids, class_ids, school_id } = selections
    const selection = user_ids && user_ids.length ? {user_ids} : class_ids && class_ids.length ? {class_ids} : {school_id}
    updateModule(moduleKey, {value, ...selection})
  }
  
  // componentDidMount
  useEffect(() => {
    getModule(moduleKey);
  }, [])

  return (
    <Fragment>
      <Row>
        <Col sm={8}>
          <strong>{title}</strong>
          <p>{description}</p>
        </Col>
        <Col sm={4} style={{ textAlign: "end", alignSelf: "center" }}>
          {loading && !updating &&
            <Fragment><InlineSync loading={true}/>{' '}</Fragment>
          }
          <ButtonGroup>
            <Button color="primary" outline={canDisable} disabled={!canDisable} onClick={() => handleToggle(false)}>
              OFF
              {updating && updatingValue === false &&
                <Fragment>{' '}<InlineSync loading={true}/></Fragment>
              }
            </Button>
            <Button color="primary" outline={canEnable} disabled={!canEnable} onClick={() => handleToggle(true)}>
              ON
              {updating && updatingValue === true &&
                <Fragment>{' '}<InlineSync loading={true}/></Fragment>
              }
            </Button>
          </ButtonGroup>
        </Col>
      </Row>
      <hr />
    </Fragment>
  )
}
const mapStateToProps = ({ base }, props) => {
  const { modules, bases: allBases, platoons: allPlatoons, soldiers: allSoldiers } = base
  const { moduleKey, selectionScope} = props;
  const selectionIds = props.selectionIds ? props.selectionIds.map(id => parseInt(id, 10)) : []
  const module = modules[moduleKey]
  const loading = !module || module.loading || (
    selectionScope === "school" ? allBases.loading || allPlatoons.loading || allSoldiers.loading
    : selectionScope === "class" ? allPlatoons.loading || allSoldiers.loading
    : selectionScope === "user" ? allSoldiers.loading
    : false
  )

  const updating = module && typeof module.loading === 'object'
  const updatingValue = updating ? module.loading.value : undefined

  const recordIdsToInt = r => ({
    ...r,
    ...(r.user_id ? {user_id: parseInt(r.user_id, 10)} : {}),
    ...(r.class_id ? {class_id: parseInt(r.class_id, 10)} : {}),
    ...(r.school_id ? {school_id: parseInt(r.school_id, 10)} : {})
  })

  const isRecordSelected = r => selectionIds.includes(r[selectionScope+"_id"])

  const bases = ["school"].includes(selectionScope) ? allBases.bases.map(recordIdsToInt).filter(isRecordSelected) : []
  const platoons = ["school", "class"].includes(selectionScope) ? allPlatoons.platoons.map(recordIdsToInt).filter(isRecordSelected) : []
  const soldiers = selectionScope ? allSoldiers.soldiers.map(recordIdsToInt).filter(isRecordSelected) : []

  // ensure everything is loaded and there are soldiers, classes, or users to enable
  const canEnable = !loading && module.users && !!(
    bases.find(b=> module.schools[b.school_id] !== true)
    || platoons.find(p=> module.classes[p.class_id] !== true)
    || soldiers.find(s=> module.users[s.user_id] !== true)
  )

  // ensure everything is loaded and there are soldiers, classes, or users to disable
  const canDisable = !loading && module.users && !!(
    bases.find(b=> module.schools[b.school_id] !== false)
    || platoons.find(p=> module.classes[p.class_id] !== false)
    || soldiers.find(s=> module.users[s.user_id] !== false)
  )


  return { module, loading, updating, updatingValue, bases, platoons, soldiers, canEnable, canDisable }
}

const mapDispatchToProps = { getModule, updateModule }

export default connect(mapStateToProps, mapDispatchToProps)(ModuleControlRow)