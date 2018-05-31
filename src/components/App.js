import React, { Component } from 'react';
import Sidebar, { getMenu } from 'components/navigation/Sidebar';
import logo from 'img/logo.svg';
import 'styles/App.css';

class App extends Component {
  state = { active: false }

  toggle = () => {
    this.setState({
      active: !this.state.active
    });
  }

  componentDidMount() {
    if ( window.innerWidth > 768 ) {
      this.setState({ active: true });
    }
  }

  render() {
    return (
      <div id="App">
        <Sidebar menu={ getMenu() } active={ this.state.active } />
        <div id="content">
          <header className="App-header">
            <img src={logo} className="App-logo" alt="logo" />
            <h1 className="App-title">Welcome to React</h1>
          </header>
          <p className="App-intro">
            To get started, edit <code>src/App.js</code> and save to reload.
          </p>
          <button style={ { float: "right" } } onClick={ this.toggle }>Toggle</button>
        </div>
      </div>
    );
  }
}

export default App;
