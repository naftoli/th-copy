import React, { Component } from 'react';
import { connect } from 'react-redux';

class TeacherMarkPage extends Component {

  render() {
    return (
      <div id='TeacherMarkPage'>
        <p>Coming Soon!</p>
      </div>
    );
  }
}

const mapStateToProps = () => {
  return {}
};

const mapDispatchToProps = {};

export default connect( mapStateToProps, mapDispatchToProps )( TeacherMarkPage );
