import React, { Component } from 'react';
// components
import ReactTable from 'react-table';
// functions
import is from 'is_js';
import { filter, scrollToTop } from 'functions/tables';

export class Table extends Component {
  render () {
    const { pageId, loading } = this.props;
    const onChange = scrollToTop( pageId );

    const defaultProps = {
      className: "-striped -highlight", 
      noDataText: loading ? 'Loading...' : 'No Data',
      filterable: true, defaultFilterMethod: filter,
      minRows: 5, defaultPageSize: is.mobile() || is.tablet() ? 25 : 100,
      onPageChange: onChange, onFilteredChange: onChange,
    }

    return <ReactTable {...defaultProps} { ...this.props }/>;
  }
}
