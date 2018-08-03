import React, { Component } from 'react';
// components
import { Callout } from 'components/ui';
import { Button, ButtonGroup } from 'reactstrap'; 
import ReactTable from "react-table";
import checkboxHOC from "react-table/lib/hoc/selectTable";
// functions
import is from 'is_js';
import classnames from 'classnames';
import { toast } from 'react-toastify';
import { loginChanged } from 'functions/login';
import { arrayToCSV, setTitle } from 'functions/utils';
import { filter, scrollToTop } from 'functions/tables';
import { getSoldiers } from 'store/soldiers/registration/operations';
// styles
import './RegistrationPage.scss';

const CheckboxTable = checkboxHOC(ReactTable);

export class RegistrationPage extends Component {

  state = { 
    loading: false, soldiers: [],
    selection: [], selectAll: false
  }

  checkboxTable = React.createRef();
  
  componentDidMount(){ 
    setTitle('Soldier Registration');
    this.getSoldiers(); 
  }

  getSoldiers = () => {
    this.setState({ loading: true });
    getSoldiers()
    .then( soldiers => {
      this.setState({ loading: false, soldiers });
    }).catch( error => {
      this.setState({ loading: false });
      toast.error( error.message );
    })
  }

  toCSV = () => { debugger; }

  isSelected = user_id => {
    return this.state.selection.includes( user_id );
  };

  toggleSelection = (user_id, shift, row) => {
    // start off with the existing state ( with a new array to avoid errors )
    let selection = [ ...this.state.selection ];
    const keyIndex = selection.indexOf( user_id );
    // check to see if the key exists
    if (keyIndex >= 0) { // it does exist so we will remove it using destructing
      selection = [
        ...selection.slice(0, keyIndex),
        ...selection.slice(keyIndex + 1)
      ];
    } else { // it does not exist so add it
      selection.push(user_id);
    }
    this.setState({ selection });
  };

  toggleAll = () => {
    // uses HOC to select all the currently visiable users in all pages ( not the ones filtered out )
    const selectAll = this.state.selectAll ? false : true;
    const selection = [];
    if (selectAll) {
      // we need to get at the internals of ReactTable
      const wrappedInstance = this.checkboxTable.current.getWrappedInstance();
      // the 'sortedData' property contains the currently accessible records based on the filter and sort
      const currentRecords = wrappedInstance.getResolvedState().sortedData;
      // we just push all the IDs onto the selection array
      currentRecords.forEach(item => {
        selection.push(item._original._id);
      });
    }
    this.setState({ selectAll, selection });
  };

  render() {
    const { loading, soldiers, selectAll } = this.state;
    const { getSoldiers, toCSV, isSelected, toggleSelection, toggleAll } = this;
    const data = soldiers.map( soldier => ({ ...soldier, _id: soldier.user_id }));

    let columns = [
      { Header: "First Name", accessor: 'first' },
      { Header: "Last Name", accessor: 'last' },
      { Header: "Serial Number", accessor: 'user_serial' },
    ]

    const checkboxProps = {
      selectAll, isSelected, selectType: "checkbox",
      toggleSelection, toggleAll, getTrProps: (s, r) => {
        const selected = r ? this.isSelected(r.original._id) : false;
        return {
          className: selected ? "selected-row" : ""
        };
      }
    };

    return (
      <div id='RegistrationPage'>
        {/* User Guide */}
        <Callout title='Soldier Registration'>
          All of your Soldiers are displayed below.<br/>Please select the Soldiers you are registering.
        </Callout>
        {/* Action buttons */}
        <ButtonGroup style={{ margin: '10px 0px', width: '100%', justifyContent: 'flex-end' }}>
          <Button color="primary" onClick={ getSoldiers }>
            <i className={`fas fa-redo-alt ${ !loading || 'fa-spin' }`}></i> Refresh
          </Button>
          { is.not.edge() && is.not.ie() && is.not.ios() &&
            <Button color="primary" onClick={ toCSV }>
              <i className="fas fa-file-download" /> Save Soldier Registration List
            </Button>
          }
        </ButtonGroup>
        { soldiers.length > 0 &&
          <CheckboxTable data={ data } columns={ columns } filterable={true} className="-striped -highlight" 
            noDataText={ loading ? 'Loading...' : 'No Data' } defaultFilterMethod={ filter }
            onPageChange={ scrollToTop('RegistrationPage') } onFilteredChange={ scrollToTop('RegistrationPage') }
            { ...checkboxProps } ref={this.checkboxTable} />
        }
        <pre><code>{ JSON.stringify( soldiers, null, 2) }</code></pre>
      </div>
    )
  }
}

export default RegistrationPage;