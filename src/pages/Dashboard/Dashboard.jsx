import React, { Component } from 'react';
import Sidebar, { getMenu } from 'components/navigation/Sidebar';
import Navbar from 'components/navigation/Navbar';
import { withRouter } from 'react-router';
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
    return (
      <div id="dashboard">
        <Navbar onClick={ this.toggle } />
        <div id="dashboard-body">
          <Sidebar menu={ getMenu() } active={ this.state.active } />
          <div id="dashboard-content">
            { this.props.children }
          </div>
        </div>
      </div>
    )
  }
}

export default withRouter(Dashboard);