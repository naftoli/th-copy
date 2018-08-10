import React, { Component } from 'react';
import { Link } from 'react-router-dom';
// components
import ReactTable from 'react-table';
// functions
import { defaultTableProps } from 'functions/tables';

class Step2 extends Component {

  render() {
    const { soldiers, loading, toggleRowBasic, toggleAllBasic } = this.props;

    let columns = [
      {
        Header: "First Name", accessor: 'first',
        Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
      },{
        Header: "Last Name", accessor: 'last',
        Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
      },{
        Header: "Serial Number", accessor: 'user_serial',
        Cell: props => <Link to={`/users/${props.original.user_id}`}>{props.value}</Link>,
      },{ Header: 'Transitioning To', accessor: 'transition' },
    ];

    let tableProps = defaultTableProps( 'step-2', loading );
    tableProps = { ...tableProps, 
      data: loading ? [] : soldiers, columns, minRows: 4,
    }

    return (
      <div id='step-2'>
        <p className="title">Step 2: Select Soldiers</p>
        <ReactTable { ...tableProps }/>
      </div>
    );
  }
}

export default Step2;
