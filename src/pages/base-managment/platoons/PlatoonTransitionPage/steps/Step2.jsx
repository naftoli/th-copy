import React, { Component } from 'react';
import { Link } from 'react-router-dom';
// components
import { SelectTable } from 'components/ui';

class Step2 extends Component {
  // refs
  checkAll = null;
  checkboxTable = React.createRef();
  // data
  columns = [
    { Header: "First Name", accessor: 'first',
      Cell: props => <Link to={`/bm/users/${props.original.user_id}`}>{props.value}</Link>,
    },
    { Header: "Last Name", accessor: 'last',
      Cell: props => <Link to={`/bm/users/${props.original.user_id}`}>{props.value}</Link>,
    },
    { Header: "Serial Number", accessor: 'user_serial',
      Cell: props => <Link to={`/bm/users/${props.original.user_id}`}>{props.value}</Link>,
    },
    { Header: 'Transitioning To', accessor: 'transition' },
  ];
  // functions
  getId = item => item.user_id;
  toggle = ( selection ) => this.props.updateSelection( selection );
  // render
  render() {
    const { soldiers, loading, selection } = this.props;

    return (
      <div id='step-2'>
        <p className="title">Step 2: Select Soldiers</p>
        <SelectTable 
          pageId='RegistrationPage' 
          columns={ this.columns }
          loading={ loading }
          data={ loading ? [] : soldiers } 
          
          getId={ this.getId }
          selection={ selection }
          maxSelectionSize={ soldiers.length }
          toggleRow={ this.toggle }
          toggleAll={ this.toggle } />
      </div>
    );
  }
}

export default Step2;
