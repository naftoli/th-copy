import React, { Component } from 'react';
import { connect } from 'react-redux';
import PropTypes from 'prop-types';
// functions
import { loginStoreChanged, isAdmin } from 'functions/login';
// state
import { getBases } from 'store/bases/operations';

class BasesPage extends Component {

  static propTypes = {};

  componentDidMount() { this.loadBases(); }

  componentDidUpdate({ login }) {
    if ( loginStoreChanged( login ) ) this.loadBases();
  }

  loadBases = () => {
    const { login, history, match } = this.props;
    if ( !isAdmin( login.code ) ) { 
      history.replace( `${match.path}/${login.id}` ); 
    }
    this.props.getBases();
  }

  render() {
    console.log( this.props.history );
    return (
      <div id='BasesPage'>
        <pre>
          { JSON.stringify( this.props, null, 2 ) }
        </pre>
      </div>
    );
  }
}

const mapStateToProps = ({ bases, login }) => ({
  ...bases,
  login: login.current_login
});

const mapDispatchToProps = { getBases };

export default connect(
  mapStateToProps, mapDispatchToProps
)( BasesPage );
