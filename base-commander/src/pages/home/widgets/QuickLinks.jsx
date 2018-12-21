import React, { Component } from 'react';

import { Link } from 'react-router-dom';
import { FontAwesome } from 'components/ui';
import { LEGACY_URL } from 'components/constants';
// components
import { Col } from 'reactstrap';

export class QuickLinks extends Component {

  render() {
    return (
      <Col xs={12} sm={6} xl={4}>
        <div id='QuickLinks' className='widget'>
          <h2>Quick Links</h2>

          <div id='links'>
            <Link to='/missions/print'>
              <FontAwesome icon='print' />
              Print Missions
            </Link>
            <Link to='/missions/mark'>
              <FontAwesome icon='check-circle' regular />
              Mark Missions
            </Link>
          </div>
        </div>
      </Col>
    );
  }
}

export class Resources extends Component {

  render() {
    return (
      <Col xs={{ size: 12, order: 12 }} xl={{ size: 4, order: 0 }}>
        <div id='Resources' className='widget'>
          <h2>Resources</h2>

          <div id='links'>
            <a href='//dropbox.com/sh/c2g76cp76it1bf6/AABw7AHHEKWfahv-yIFXV8Qsa?dl=0' target='_blank' rel="noopener noreferrer">
              <img src={`${LEGACY_URL}/homeIcons/dropbox.svg`} alt='hachayol'/>
              Resources
            </a>
            <a href='//dropbox.com/sh/41u2regs73kfp9h/AACJV58J9KD6elXXZisYz74Ia?dl=0' target='_blank' rel="noopener noreferrer">
              <img src={`${LEGACY_URL}/homeIcons/tanya.png`} alt='tanya'/>
              Tanya
            </a>
            <a href='//dropbox.com/s/9h5k3bqrvm1qrjr/CTH%20-%20BC%20Calendar%205779%20with%20bleed%20HR.pdf?dl=0' target='_blank' rel="noopener noreferrer">
              <img src={`${LEGACY_URL}/homeIcons/Calendar.png`} alt='calendar'/>
              Calendar
            </a>
            <a href='//dropbox.com/sh/ztiltfbvpo4te9p/AABZjQmM71L5YESXllu1xjrIa?dl=0' target='_blank' rel="noopener noreferrer">
              <img src={`${LEGACY_URL}/homeIcons/Chidon.png`} alt='chidon'/>
              Yahadus
            </a>
          </div>
        </div>
      </Col>
    );
  }
}
