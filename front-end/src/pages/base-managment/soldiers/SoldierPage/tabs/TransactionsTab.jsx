import React, { useState, useEffect, useRef } from 'react';
import { TabPane, Form, Row, Col, Input, Button } from 'reactstrap';
import { Checkbox } from 'components/inputs';
import { toast } from 'react-toastify';
import API from 'api/api';
import ReactTable from 'react-table';
import JewishDatepicker from 'heDatePicker/dist/js/he-datepicker.js';
import 'heDatePicker/dist/css/he-datepicker.css';

export const TransactionsTab = ({ soldier, tabId }) => {
  const [pointsHistory, setPointsHistory] = useState([]);
  const [loaded, setLoaded] = useState(false);
  const [loading, setLoading] = useState(false);
  const [types, setTypes] = useState(['achievements', 'store', 'tasks', 'manual']);

  // Refs for datepicker cleanup
  const fromDatepickerRef = useRef(null);
  const toDatepickerRef = useRef(null);

  useEffect(() => {
    // Initialize datepickers
    const initDatepickers = () => {
      fromDatepickerRef.current = new JewishDatepicker('#from', {
        hideHeader: true,
        color: '#5e72e4'
      });

      toDatepickerRef.current = new JewishDatepicker('#to', {
        hideHeader: true,
        color: '#5e72e4'
      });

      // Position the datepickers correctly
      setTimeout(() => {
        const datepickers = document.getElementsByClassName('jewish_datepicker_wrapper');
        const from = document.getElementById('from');
        const to = document.getElementById('to');

        if (from && to && datepickers.length >= 2) {
          // Move datepickers to be children of their input containers 
          // (Note: this DOM manipulation isn't ideal in React, but maintaining existing behavior)
          if (from.parentNode) from.parentNode.insertBefore(datepickers[0], from.nextSibling);
          if (to.parentNode) to.parentNode.insertBefore(datepickers[1], to.nextSibling);

          // Position the datepickers
          datepickers[0].style.position = 'absolute';
          datepickers[0].style.zIndex = '9';
          datepickers[0].style.top = '70px';
          datepickers[0].style.left = '20px';

          datepickers[1].style.position = 'absolute';
          datepickers[1].style.zIndex = '9';
          datepickers[1].style.top = '70px';
          datepickers[1].style.left = '20px';
        }
      }, 100);
    };

    initDatepickers();

    return () => {
      // Cleanup
      if (fromDatepickerRef.current && typeof fromDatepickerRef.current.cleanupClickOutside === 'function') {
        fromDatepickerRef.current.cleanupClickOutside();
      }
      if (toDatepickerRef.current && typeof toDatepickerRef.current.cleanupClickOutside === 'function') {
        toDatepickerRef.current.cleanupClickOutside();
      }
    }
  }, []);

  const convertDate = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-indexed
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`
  }

  const julianDayToJSDate = (jd) => {
    const jsEpochJD = 2440587.5;
    const milliseconds = (jd - jsEpochJD) * 24 * 60 * 60 * 1000;
    return new Date(milliseconds);
  }

  const jdToGregorian = (jd) => {
    return convertDate(julianDayToJSDate(jd));
  }

  const dateOnly = (date) => {
    if (!date) return '';
    return date.split(' ')[0];
  }

  // handle type change
  const updateType = (e) => {
    const val = e.target.value;
    const checked = e.target.checked;
    if (checked) {
      setTypes(prev => [...prev, val]);
    } else {
      setTypes(prev => prev.filter(t => t !== val));
    }
  }

  // handle form submission
  const handleSubmit = e => {
    e.preventDefault();
    const { from, to } = e.target;

    const dateFrom = new Date("2010-01-01")
    const defaultFrom = convertDate(dateFrom)

    const dateTo = new Date()
    const defaultTo = convertDate(dateTo)

    setLoading(true);
    setLoaded(false);

    // Get the Gregorian date values from the data attributes
    const fromDate = from.getAttribute('data-gregorian-date') || defaultFrom;
    const toDate = to.getAttribute('data-gregorian-date') || defaultTo;

    const api = '/core/users?action=getTransactions';
    API.post(api, {
      user_id: soldier.user_id,
      from: fromDate || defaultFrom,
      to: toDate || defaultTo,
      types: types
    })
      .then(response => {
        // response is an object with keys as dates and values as arrays of transactions
        // convert into array of transactions without the date and flatten the array
        let keys = Object.keys(response)
        let history = keys.map(key => {
          return response[key]
        })
        history = history.flat()

        setPointsHistory(history);
        setLoaded(true);
        setLoading(false);
      })
      .catch(error => {
        setLoading(false);
        toast.error(error.message || 'Failed to load points history');
        console.error('Points history error:', error);
      });
  }

  const transactionTypes = {
    'admin': 'Admin Action',
    'admin_users_manual': 'Admin Adjustment',
    'direct_transfer': 'Direct Transfer',
    'specific achievement card': 'Achievement Card',
    'store': 'Store Purchase',
    'transaction_manager_store': 'Store Adjustment',
    'short_name': 'Task'
  }

  return (
    <TabPane id='PointsTab' tabId={tabId}>
      <p className='title'>Transactions</p>
      <p>
        Search for transactions between two dates. If you want to see from a specific date
        to the current date, leave the "To" field blank. If you want to see all transactions,
        leave "From" and "To" fields blank.
      </p>
      <Form id='points-form' onSubmit={handleSubmit}>
        <Row>
          <Col sm={6}>
            <p>From: <Input type='text' name='from' id='from' /></p>
          </Col>
          <Col sm={6}>
            <p>To: <Input type='text' name='to' id='to' /></p>
          </Col>
        </Row>
        <Row>
          <Col sm={12}>
            <p>
              Type of Transaction:
              <Checkbox
                name='type'
                value='achievements'
                checked={types.includes('achievements')}
                onChange={updateType}
              />Achievement Cards&nbsp;&nbsp;&nbsp;
              <Checkbox
                name='type'
                value='store'
                checked={types.includes('store')}
                onChange={updateType}
              />Store&nbsp;&nbsp;&nbsp;
              <Checkbox
                name='type'
                value='tasks'
                checked={types.includes('tasks')}
                onChange={updateType}
              />Tasks&nbsp;&nbsp;&nbsp;
              <Checkbox
                name='type'
                value='manual'
                checked={types.includes('manual')}
                onChange={updateType}
              />Admin/BC Adjustments
            </p>
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
              id: 'date',
              Header: 'Date',
              accessor: row => dateOnly(row.created) || jdToGregorian(row.mark_date),
            },
            {
              Header: 'Points',
              accessor: 'points'
            },
            {
              id: 'description',
              Header: 'Description',
              accessor: row => row.resource_name || ('Task - ' + row.short_name), // Use function instead of array
              Cell: ({ value }) => transactionTypes[value] || value
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
