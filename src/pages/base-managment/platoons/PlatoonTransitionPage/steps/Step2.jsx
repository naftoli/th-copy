import React, { Component } from 'react';
import { Link } from 'react-router-dom';
// components
import ReactTable from 'react-table';
import { Checkbox } from 'components/ui';
// functions
import {
  defaultTableProps, toggleRowBasic, toggleAllBasic
} from 'functions/tables';

class Step2 extends Component {
  // state
  state = { selectAll: false }
  // refs
  checkAll = null;
  checkboxTable = React.createRef();
  // data
  columns = [
    { id: 'checkbox', accessor: '', width: 38,
      filterable: false, sortable: false, resizable: false,
      Cell: props => <Checkbox onChange={() => {}} checked={this.isSelected( this.getId(props.original) )} />, 
      Header: props => <Checkbox onChange={this.toggleAll} 
        checked={this.state.selectAll} setRef={ref => {this.checkAll = ref}} />
    }, {
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
  // functions
  isSelected = user_id => this.props.selection.includes( user_id );
  getId = item => item.user_id;
  toggleAll = () => {
    toggleAllBasic( this.state.selectAll, this.checkboxTable.current, this.getId )
    .then( this.updateSelection );
  };
  updateSelection = ({ selection, selectAll }) => { 
    this.setState({ selectAll });
    this.props.updateSelection( selection )
  }
  // render
  render() {
    const { soldiers, loading, selection } = this.props;

    let toggleRow = toggleRowBasic( selection, soldiers.length, this.checkAll );
    // props for each row
    const getTrProps = ( state, row ) => {
      const selected = row ? this.isSelected( this.getId(row.original) ) : false;
      return {
        onClick: e => { e.preventDefault(); toggleRow( this.getId(row.original) ).then( this.updateSelection ); },
        className: selected ? "selected-row" : ""
      }
    }

    let tableProps = defaultTableProps( 'step-2', loading );
    tableProps = { ...tableProps, 
      getTrProps,
      data: loading ? [] : soldiers, 
      columns: this.columns, minRows: 4,
    }

    return (
      <div id='step-2'>
        <p className="title">Step 2: Select Soldiers</p>
        <ReactTable { ...tableProps } ref={this.checkboxTable} />
      </div>
    );
  }
}

export default Step2;
