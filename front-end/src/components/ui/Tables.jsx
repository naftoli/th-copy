import React, { useState, useRef } from 'react';
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

export const Table = (props) => {
  const { pageId } = props;
  const onChange = scrollToTop(pageId);

  const defaultProps = {
    ...defaultTableProps,
    onPageChange: onChange, onFilteredChange: onChange,
  }

  return <ReactTable {...defaultProps} {...props} />;
}

export const SelectTable = ({
  getId, toggleRow: toggleRowProp, selection, maxSelectionSize, checkboxTogglesRow,
  // props passed to ReactTable
  pageId, columns, toggleAll: toggleAllProp, ...props
}) => {

  const [selectAll, setSelectAll] = useState(false);
  const checkboxTable = useRef(null);
  const checkAll = useRef(null);

  // check if item is selected
  const isSelected = id => selection.includes(id);

  // toggle a single row
  const toggleRow = (row) => {
    // start off with the existing state ( with a new array to avoid errors )
    let newSelection = [...selection];
    const id = getId(row);
    const keyIndex = newSelection.indexOf(id);

    // check to see if the key exists
    if (keyIndex >= 0) // it does exist so we will remove it using destructing
      newSelection = [...newSelection.slice(0, keyIndex), ...newSelection.slice(keyIndex + 1)];
    else // it does not exist so add it
      newSelection.push(id);

    if (checkAll.current) {
      checkAll.current.indeterminate = maxSelectionSize > newSelection.length && newSelection.length > 0;
    }

    const isAllSelected = maxSelectionSize === newSelection.length;
    setSelectAll(isAllSelected);

    // callback to parent component
    if (toggleRowProp)
      toggleRowProp(newSelection, row);
  }

  const toggleAll = () => {
    // return variables
    const newSelection = [];
    let currentRecords = [];
    // uses HOC to select all the currently visiable users in all pages ( not the ones filtered out )
    const isSelectingAll = !selectAll; // Toggle state

    if (isSelectingAll) {
      // get all Id's currently not filtered out
      if (checkboxTable.current) {
        currentRecords = checkboxTable.current.getResolvedState().sortedData;
        // we just push all the IDs onto the selection array
        currentRecords.forEach(item => {
          newSelection.push(getId(item._original));
        });
      }
    }
    setSelectAll(isSelectingAll);
    if (toggleAllProp)
      toggleAllProp(newSelection, currentRecords);
  };

  const getTrProps = (state, row) => {
    const selected = row ? isSelected(getId(row.original)) : false;

    return {
      onClick: e => { e.preventDefault(); if (row) toggleRow(row.original); },
      className: classnames('selectable', { 'selected-row': selected })
    }
  }

  const tableColumns = [
    {
      id: 'checkbox', accessor: '', width: 38,
      filterable: false, sortable: false, resizable: false,
      Cell: ({ original }) => <Checkbox onChange={checkboxTogglesRow ? () => toggleRow(original) : () => { }}
        checked={isSelected(getId(original))} />,
      Header: () => <Checkbox onChange={toggleAll}
        checked={selectAll} setRef={ref => { checkAll.current = ref }} />
    },
    ...columns
  ]

  // setup default props
  const onChange = scrollToTop(pageId);
  const defaultProps = {
    ...defaultTableProps, getTrProps,
    onPageChange: onChange, onFilteredChange: onChange,
  }

  return (
    <ReactTable
      {...defaultProps}
      {...props}
      columns={tableColumns}
      ref={checkboxTable} />
  );
}

SelectTable.propTypes = {
  // get the id from a row
  getId: PropTypes.func.isRequired,
  // callback when row is toggled
  toggleRow: PropTypes.func,
  // array of selected id's from getId
  selection: PropTypes.array.isRequired,
  maxSelectionSize: PropTypes.number.isRequired,
  // when true, clicking the checkbox toggles selection
  checkboxTogglesRow: PropTypes.bool
}
