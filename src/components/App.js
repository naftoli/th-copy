import React, { Component } from 'react';
import Sidebar from 'components/navigation/Sidebar';
import logo from 'img/logo.svg';
import 'styles/App.css';

class App extends Component {
  render() {
    return (
      <div id="App">
        <Sidebar />
        <div id="content">
          <header className="App-header">
            <img src={logo} className="App-logo" alt="logo" />
            <h1 className="App-title">Welcome to React</h1>
          </header>
          <p className="App-intro">
            To get started, edit <code>src/App.js</code> and save to reload.
          </p>
        </div>
      </div>
    );
  }
}

export default App;
