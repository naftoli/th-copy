import React, { Component } from 'react';
// import { connect } from 'react-redux';
// import PropTypes from 'prop-types';
// state
// import { getBases } from 'store/bases/operations';

class BasesPage extends Component {

  static propTypes = {};

  render() {
    return (
      <div id='BasePage'>
        <pre>
          { JSON.stringify( this.props, null, 2 ) }
        </pre>
      </div>
    );
  }
}

export default BasesPage;
