import React, { Component } from 'react';
import Sidebar, { getMenu } from 'components/navigation/Sidebar';
import Navbar from 'components/navigation/Navbar';
import { withRouter } from 'react-router';
import { connect } from 'react-redux';
import './Dashboard.scss';

export class Dashboard extends Component {

  static defaultProps = {
    history: { listen: () => { return () => {} } } // function that returns a function
  }

  constructor( props ){
    super( props );
    this.state = { active: false }
  }

  toggle = () => {
    // only toggle the sidebar if it will change the state
    if ( window.innerWidth <= 1024 ) {
      this.setState({ active: !this.state.active });
    }
  }

  componentDidMount() {
    // open the sidebar by default on larger displays
    if ( window.innerWidth > 768 ) {
      this.setState({ active: true });
    }
    // close the sidebar if the route changes
    this.unlisten = this.props.history.listen( () => {
      if ( window.innerWidth <= 768 ) {
        this.setState({ active: false });
      }
    });
  }

  componentWillUnmount() {
    this.unlisten();
  }

  render() {
    const { current_user } = this.props;
    return (
      <div id="dashboard">
        <Navbar onClick={ this.toggle } logins={ current_user.logins } />
        <div id="dashboard-body">
          <Sidebar menu={ getMenu( current_user.auth_code ) } active={ this.state.active } />
          <div id="dashboard-content">
            { this.props.children }
          </div>
        </div>
      </div>
    )
  }
}

const mapStateToProps = ( state ) => ({
  current_user: state.login.current_user
});

export default withRouter(
  connect( mapStateToProps )( Dashboard )
);