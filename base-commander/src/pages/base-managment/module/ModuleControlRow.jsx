import React, { Fragment, useEffect } from 'react';
import { connect } from 'react-redux';
// components
import { Row, Col, Button, ButtonGroup } from 'reactstrap';
import { InlineSync } from 'components/ui';
// state
import { getModule, updateModule } from 'store/base/modules/operations';


function ModuleControlRow (props) {
  const {
    moduleKey, title, module, loading, moduleLoading, selections,
    canEnable, canDisable,
    getModule, updateModule
  } = props

  const updateSetting = (value) => {
    updateModule(moduleKey, selections)
  }
  
  // componentDidMount
  useEffect(() => {
    // console.log("in ModuleControlRow useEffect")
    console.log(getModule)
    getModule(moduleKey);
  }, [])

  return (
    <Fragment>
      <Row>
        <Col sm={6}>
          {title}
        </Col>
        <Col sm={6} style={{ textAlign: "end" }}>
          <InlineSync loading={moduleLoading}/>
          <ButtonGroup>
            <Button color="primary" onClick={() => updateSetting(false)} disabled={!canDisable}>
              OFF
            </Button>
            <Button color="primary" onClick={() => updateSetting(true)} disabled={!canEnable}>
              ON
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
  const { moduleKey, selectionScope, selectionIds} = props
  const module = modules[moduleKey]
  console.log("modules", modules)
  const loading = allBases.loading || allPlatoons.loading || allSoldiers.loading
  const moduleLoading = !module || module.loading

  const isRecordSelected = r => {
    // console.log(r, selectionScope+"_id", r[selectionScope+"_id"])
    return selectionIds.includes(r[selectionScope+"_id"])
  }
  const bases = ["school"].includes(selectionScope) ? allBases.bases.filter(isRecordSelected) : []
  const platoons = ["school", "class"].includes(selectionScope) ? allPlatoons.platoons.filter(isRecordSelected) : []
  const soldiers = selectionScope ? allSoldiers.soldiers.filter(isRecordSelected) : []

  console.log(bases)
  console.log(platoons)
  console.log(soldiers)

  if ( selectionScope === null) { console.log("fix selectionScope")}
  const canEnable = bases.find(b=> module.schools[b.school_id] !== true)
    || platoons.find(p=> module.classes[p.class_id] !== true)
    || soldiers.find(s=> module.users[s.user_id] !== true)

  const canDisable = bases.find(b=> module.schools[b.school_id] !== false)
    || platoons.find(p=> module.classes[p.class_id] !== false)
    || soldiers.find(s=> module.users[s.user_id] !== false)

  return { module, moduleLoading, loading, bases, platoons, soldiers, canEnable, canDisable }
}

const mapDispatchToProps = { getModule, updateModule }

export default connect(mapStateToProps, mapDispatchToProps)(ModuleControlRow)