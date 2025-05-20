import React, { Component } from 'react';
import { TabPane } from 'reactstrap';
import { Form, Row, Col, Input, Button } from 'reactstrap';
import { toast } from 'react-toastify';
import API from 'api/api';
import ReactTable from 'react-table';

class TransactionsTab extends Component {
  // initial state
  state = {
    pointsHistory: [],  // Changed to object since it seems to be keyed by date
    loaded: false,
    loading: false,  // Added loading state
  }

  convertDate = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`
  }

  // handle form submission
  handleSubmit = e => {
    e.preventDefault();
    const { from, to } = e.target;
    
    const dateFrom = new Date("2020-01-01")
    const defaultFrom = this.convertDate(dateFrom)

    const dateTo = new Date()
    const defaultTo = this.convertDate(dateTo)

    this.setState({ loading: true, loaded: false });
    
    const api = '/core/users?action=getTransactions';
    API.post(api, {
        user_id: this.props.soldier.user_id,
        from: from.value || defaultFrom,
        to: to.value || defaultTo
    })
    .then(response => {
      // response is an object with keys as dates and values as arrays of transactions
      // convert into array of transactions without the date and flatten the array
      let keys = Object.keys(response)
      let history = keys.map(date => {
        return response[date]
      })
      history = history.flat()
      console.log(history);
      this.setState({ 
        pointsHistory: history,
        loaded: true,
        loading: false 
      });
    })
    .catch(error => {
      this.setState({ loading: false });
      toast.error(error.message || 'Failed to load points history');
      console.error('Points history error:', error);
    });
  }

  render() {
    const { pointsHistory, loaded, loading } = this.state;

    const transactionTypes = {
        'admin': 'Admin Action', 
        'admin_users_manual': 'Admin Adjustment',
        'direct_transfer': 'Direct Transfer',
        'specific achievement card': 'Achievement Card',
        'store': 'Store Purchase',
        'transaction_manager_store': 'Store Adjustment'
    }
    
    return (
      <TabPane id='PointsTab' tabId={this.props.tabId}>
        <p className='title'>Transactions</p>
        <p>
            Search for transactions between two dates. If you want to see from a specific date 
            to the current date, leave the "To" field blank. If you want to see all transactions, 
            leave "From" and "To" fields blank.
        </p>
        <Form id='points-form' onSubmit={this.handleSubmit}>
          <Row>
            <Col sm={6}>
              <p>From: <Input type='date' name='from' /></p>
            </Col>
            <Col sm={6}>
              <p>To: <Input type='date' name='to' /></p>
            </Col>
          </Row>
          <Row>
            <Col sm={12}>
              <Button type='submit' color='primary' disabled={loading}>
                {loading ? 'Loading...' : 'Search'}
              </Button>
            </Col>
          </Row>
        </Form>

        {loading || loaded ? (
          <p className='title'>Transaction History</p>
        ) : null}
        
        {loading && <p>Loading transactions...</p>}
        
        {!loading && pointsHistory.length > 0 && (
          <ReactTable
            data={pointsHistory}
            columns={[
              {
                Header: 'Date',
                accessor: 'created'
              },
              {
                Header: 'Points',
                accessor: 'points'
              },
              {
                Header: 'Resource',
                accessor: 'resource_name',
                Cell: ({ value }) => transactionTypes[value]
              }
            ]}
          />
        )}
        
        {loaded && pointsHistory.length === 0 && (
          <p>No transactions found</p>
        )}
      </TabPane>
    );
  }
}

export { TransactionsTab };