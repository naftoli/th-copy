import React, { Component } from 'react';
import Sidebar, { getMenu } from 'components/navigation/Sidebar';
import Navbar from 'components/navigation/Navbar';
import 'styles/App.css';

class App extends Component {
  state = { active: false }

  toggle = () => {
    if ( window.innerWidth <= 1024 ) {
      this.setState({
        active: !this.state.active
      });
    }
  }

  componentDidMount() {
    if ( window.innerWidth > 768 &&  window.innerWidth <= 1024 ) {
      this.setState({ active: true });
    }
  }

  render() {
    return (
      <div id="wrapper">
        <Navbar onClick={ this.toggle } />
        <div id="App">
          <Sidebar menu={ getMenu() } active={ this.state.active } />
          <div id="content"></div>
        </div>
      </div>
    );
  }
}

export default App;
