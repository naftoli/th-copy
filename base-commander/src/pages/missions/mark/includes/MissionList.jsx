import React from 'react';
import { LEGACY_URL } from 'components/constants';
// components
import { FontAwesome } from 'components/ui';
import { Toggle } from 'components/inputs';
// functions 
import {
  SortableContainer,
  SortableElement,
  SortableHandle,
} from 'react-sortable-hoc';

const DragHandle = SortableHandle(() => (
  <div className='DragHandle sortable-handle'>
    <FontAwesome icon='grip-horizontal'/>
  </div>
) );

const MissionItem = SortableElement(
  ({ mission, toggleMission }) => (
    <div className='MissionItem sortable-item'>

      <DragHandle />
      
      <div className='name'>
        <img className='campaign' alt='sticker'
          src={ `${ LEGACY_URL }/images/stickers/campaigns/${ mission.subject_id }.gif`} />
        <strong>{ mission.cat } { mission.mandatory_qty >= 1 && <FontAwesome icon='star' /> }</strong><br/>
        <small>{ mission.subject_name } campaign</small>
      </div>

      <div className='miles'>
        { mission.points } Miles
      </div>

      <div className='enabled'>
        <Toggle noAnimate 
          checked={ !mission.disabled }
          onChange={ toggleMission( mission.grid_id ) } />
      </div>

    </div>
  )
);

const MissionList = ({ missions, toggleMission }) => (
  <div className='MissionList branded-scrollbar sortable-list'>

    { missions.map( ( mission, index ) => (

      <MissionItem 
        index={index} 
        mission={ mission } 
        key={`mission-${index}`} 
        toggleMission={ toggleMission } />

    ) ) }

  </div>
);

export default SortableContainer( MissionList );
