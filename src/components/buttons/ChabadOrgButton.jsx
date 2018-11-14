import React, { Component } from 'react';

export class ChabadOrgButton extends Component {

  spanref = React.createRef();

  static defaultProps = {
    onLogin: console.log
  }

  componentDidMount() {
    // check if API is present
    if ( !window.MyChabadApi )
      return false;
    // check if it has been loaded before
    if ( window.MyChabadApi.Loaded && window.MyChabadApi.CurrentNode ) {
      // load the current node into the div
      this.spanref.current.appendChild( window.MyChabadApi.CurrentNode );
      console.log( 'Chabad.org integration already loaded. Appending cached child element.' );
    } else {
      // generate the default blank node
      this.spanref.current.appendChild( this.createBlankNode() );
      window.MyChabadApi.Loaded = false;
      window.MyChabadApi.LoadViews();
      console.log( 'Chabad.org integration loaded.' );
    }

    window.MyChabadApi.Events.AddEventListener( 'statusUpdated', this.onStatusChange, this );
  }

  componentWillUnmount(){
    // check if API is present
    if ( !window.MyChabadApi )
      return false;

    // this does not seem to be working.
    // TODO wait for bugfix from chabad.org and use official api
    // window.MyChabadApi.Events.RemoveEventListener( 'statusUpdated', this.onStatusChange, this );
    // * remove the listener manually untill they fix the bug
    for ( let i = 0; i < window.MyChabadApi.Events.Listeners['statusupdated'].length; i++ ) {
      let listener = window.MyChabadApi.Events.Listeners['statusupdated'][i];
      if ( listener.callback === this.onStatusChange && listener.thisObj === this )
        window.MyChabadApi.Events.Listeners['statusupdated'].splice( i, 1 );
    }
  }
  // handle when the chabad.org status changes
  onStatusChange = event => {
    // get the data from the response
    const { Status, Key } = event.response;
    // pass the login key to the parent
    if ( Status && Key )
      this.props.onLogin( Key );
  }
  // create a blank node for the API to hook on to
  createBlankNode = () => {
    const node = document.createElement('span');
    node.className = 'mychabad';
    node.setAttribute('view', 'login');
    node.setAttribute('settings', 'viewStyle=button');
    return node;
  }

  render() {
    return (
      <div
        className='ChabadOrgButton'
        ref={ this.spanref } />
    );
  }
}
