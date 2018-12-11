import React, { Component } from 'react';
import PropTypes from 'prop-types';
// components
import ReactTable from 'react-table';
import { Checkbox } from 'components/inputs';
// functions
import is from 'is_js';
import classnames from 'classnames'
import { filter, scrollToTop } from 'functions/tables';

const defaultTableProps = {
  className: "-striped -highlight", 
  filterable: true, defaultFilterMethod: filter,
  minRows: 5, defaultPageSize: is.mobile() || is.tablet() ? 25 : 100,
}

export class Table extends Component {
  render () {
    const { pageId } = this.props;
    const onChange = scrollToTop( pageId );

    const defaultProps = {
      ...defaultTableProps,
      onPageChange: onChange, onFilteredChange: onChange,
    }

    return <ReactTable {...defaultProps} { ...this.props }/>;
  }
}

export class SelectTable extends Component {

  static propTypes = {
    // get the id from a row
    getId: PropTypes.func.isRequired,
    // callback when row is toggled
    toggleRow: PropTypes.func,
    // array of selected id's from getId
    selection: PropTypes.array.isRequired,
    maxSelectionSize: PropTypes.number.isRequired
  }

  state = { selectAll: false }

  // refs
  checkboxTable = React.createRef();
  checkAll = null; // uses callback refs. will point directly ( not using .current )

  // check if item is selected
  isSelected = id => this.props.selection.includes( id );

  // toggle a single row
  toggleRow = ( row ) => {
    // start off with the existing state ( with a new array to avoid errors )
    let selection = [ ...this.props.selection ];
    const id = this.props.getId( row );
    const keyIndex = selection.indexOf( id );

    // check to see if the key exists
    if (keyIndex >= 0) // it does exist so we will remove it using destructing
      selection = [ ...selection.slice( 0, keyIndex ), ...selection.slice( keyIndex + 1 ) ];
    else // it does not exist so add it
      selection.push( id );

    if ( this.checkAll )
      this.checkAll.indeterminate = this.props.maxSelectionSize > selection.length && selection.length > 0;

    const selectAll = this.props.maxSelectionSize === selection.length;
    this.setState({ selectAll });
    // callback to parent component
    this.props.toggleRow( selection, row );
  }

  toggleAll = () => {
    // return variables
    const selection = []; let currentRecords = [];
    // uses HOC to select all the currently visiable users in all pages ( not the ones filtered out )
    const selectAll = this.state.selectAll ? false : true;
    if ( selectAll ) {
      // get all Id's currently not filtered out
      currentRecords = this.checkboxTable.current.getResolvedState().sortedData;
      // we just push all the IDs onto the selection array
      currentRecords.forEach(item => {
        selection.push( this.props.getId ( item._original ) );
      });
    }
    this.setState({ selectAll })
    this.props.toggleAll( selection, currentRecords );
  };

  getTrProps = ( state, row ) => {
    const { isSelected, toggleRow } = this;
    const { getId } = this.props;
    const selected = row ? isSelected( getId( row.original ) ) : false;

    return {
      onClick: e => { e.preventDefault(); toggleRow( row.original ); },
      className: classnames( 'selectable', { 'selected-row': selected } )
    }
  }

  render() {
    let { pageId, columns, getId, ...props } = this.props;
    const { isSelected, getTrProps, toggleAll } = this;

    columns = [
      { id: 'checkbox', accessor: '', width: 38,
        filterable: false, sortable: false, resizable: false,
        Cell: ({ original }) => <Checkbox onChange={ () => {} } 
          checked={ isSelected( getId( original ) ) } />, 
        Header: () => <Checkbox onChange={ toggleAll } 
          checked={ this.state.selectAll } setRef={ ref => { this.checkAll = ref } } />
      },
      ...columns
    ]

    // setup default props
    const onChange = scrollToTop( pageId );
    const defaultProps = {
      ...defaultTableProps, getTrProps,
      onPageChange: onChange, onFilteredChange: onChange,
    }

    // return the table
    return (
      <ReactTable 
        {...defaultProps} 
        { ...props } 
        columns={ columns }
        ref={ this.checkboxTable } />
    );
  }
}
