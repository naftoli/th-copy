import React, { useState, useEffect, useMemo } from 'react';
import { connect } from 'react-redux';
import { useParams } from 'react-router-dom';
// components
import Child from './misc/Child';
import { Page404 } from 'pages/errors';
import { AddressRow } from 'components/rows';
import { Select, PhoneNumber } from 'components/inputs';
import { LoadingScreen, ProfilePicture, FontAwesome } from 'components/ui';
import { Row, Col, Input, Button, InputGroup } from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { setTitle } from 'functions/utils';
import { mobileLogin, isHQ } from 'functions/login';
import { getChildOptions } from './misc/functions';
import { getParents as getParentsOp, removeChild as removeChildOp, addChild as addChildOp } from 'store/base/parents/operations';
import { findOption } from 'functions/selects';

const ParentPage = (props) => {
  const { parents, getParents, removeChild, addChild, loading, login, availableChildren } = props;
  const { id } = useParams();
  const parentId = parseInt(id, 10);

  const [userSerial, setUserSerial] = useState(false);

  useEffect(() => {
    setTitle('Parent Account');
    if (parents.length === 0) {
      getParents();
    }
  }, [parents, getParents]);

  const parent = useMemo(() => {
    return parents.find(parent => parent.admin_id === parentId);
  }, [parents, parentId]);

  const loginToParent = () => {
    if (parent) {
      mobileLogin(parent.key);
    }
  };

  const handleRemoveChild = (user_serial) => {
    if (parent) {
      removeChild(parent.admin_id, user_serial)
        .catch(error => { toast.error(error.message) });
    }
  };

  const handleAddChild = () => {
    if (!userSerial) {
      toast.error('Please select a soldier to add as a child.');
    } else if (parent) {
      addChild(parent.username, userSerial)
        .then(() => setUserSerial(false))
        .catch(error => toast.error(error.message));
    }
  };

  const onChildChange = (option) => {
    setUserSerial(option && option.value);
  };

  // if we have nothing, end the function here and show a LoadingScreen
  if (loading && !parent)
    return <LoadingScreen />;
  else if (!parent)
    return <Page404 />;

  // get the info from the parent
  const {
    father_pic, mother_pic, father, mother, last,
    username, cell, mother_cell, email, children, ...address
  } = parent;

  const options = getChildOptions(availableChildren);

  return (
    <div id='ParentPage'>
      <p className='title'>Account Information</p>
      {/* Profile Pictures */}
      <Row id='profile'>
        <Col xs={6}>
          <ProfilePicture src={father_pic} alt='father' />
          <p>{father} {last}</p>
        </Col>
        <Col xs={6}>
          <ProfilePicture src={mother_pic} alt='mother' />
          <p>{mother} {last}</p>
        </Col>
      </Row>
      {/* Account information */}
      <Row>
        <Col sm={{ size: 6, order: 1 }}>
          <label>Username</label>
          <Input value={username} disabled />
        </Col>
        <Col sm={{ size: 6, order: 2 }}>
          <label>E-Mail Address</label>
          <Input value={email} disabled />
        </Col>
        <Col sm={{ size: 6, order: 3 }}>
          <label>Father's Cell Phone</label>
          {cell &&
            <PhoneNumber value={cell} disabled />
          }
          {!cell &&
            <PhoneNumber disabled />
          }
        </Col>
        <Col sm={{ size: 6, order: 4 }}>
          <label>Mother's Cell Phone</label>
          {mother_cell &&
            <PhoneNumber value={mother_cell} disabled />
          }
          {!mother_cell &&
            <PhoneNumber disabled />
          }
        </Col>
        <Col sm={{ size: 12, order: 5 }}>
          <Button color='primary' id='login' onClick={loginToParent}>
            <FontAwesome icon='sign-in-alt' /> Login to parent account.
          </Button>
        </Col>
      </Row>

      <AddressRow disabled {...address} prefix='admin_' title='Address' />

      <p className='title'>Children</p>
      <Row id='add-child'>
        {!isHQ(login.code) &&
          <Col xs='12'>
            <InputGroup>
              <Select options={options}
                onChange={onChildChange}
                value={findOption(options, userSerial) || false}
                isClearable className='react-select form-control' />

              <Button onClick={handleAddChild} color='primary' outline tabIndex={0}>
                <FontAwesome icon='user-plus' /> Add Child
              </Button>
            </InputGroup>
          </Col>
        }
      </Row>

      {parent.children.map((child, index) =>
        <Child key={index} {...child} onRemove={handleRemoveChild} />
      )}
    </div>
  );
};

const mapStateToProps = ({ base, login }) => ({
  login: login.current_login,
  parents: base.parents.parents,
  loading: base.parents.loading,
  availableChildren: base.parents.children,
})

const mapDispatchToProps = {
  getParents: getParentsOp, removeChild: removeChildOp, addChild: addChildOp
}

export default connect(
  mapStateToProps, mapDispatchToProps
)(ParentPage);
