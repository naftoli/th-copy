import React, { useState, useEffect } from 'react';
import { useDispatch } from 'react-redux';
// components
import { Link } from 'react-router-dom';
import { ButtonBar, FontAwesome } from 'components/ui';
import { Row, Col, Input, Button } from 'reactstrap';
// functions
import { toast } from 'react-toastify';
import { updateAuth, removeAuth } from 'store/base/staff/operations';
import { changeLogin } from 'store/login/actions';

const PositionRow = ({
  position: initialPosition,
  admin_id,
  auth,
  id,
  role,
  base,
  platoon: initialPlatoon
}) => {
  const dispatch = useDispatch();
  const [position, setPosition] = useState(initialPosition || '');

  useEffect(() => {
    if (initialPosition !== position && initialPosition) {
      setPosition(initialPosition);
    }
  }, [initialPosition]);

  const onPositionChanged = ({ target }) => {
    setPosition(target.value);
  }

  const update = () => {
    updateAuth({ admin_id, auth, id, position })
      .then(() => toast.info('Position Updated'))
      .catch(error => toast.error(error.message));
  }

  const remove = () => {
    dispatch(removeAuth({ admin_id, auth, id }));
  }

  // onLoginChange = () => {
  //   const { type, id } = this.props.login;
  //   console.log( type, id )
  //   changeLogin( type, id );
  // }

  let platoonContent = initialPlatoon;
  if (auth === 'class') {
    platoonContent = <Link to={`/bm/platoons/${id}`}>{initialPlatoon}</Link>;
  }

  return (
    <div className='PositionRow'>
      <Row>
        <Col xs={6} sm={4}>
          <strong>Role</strong>
          <p>{role}</p>
        </Col>
        <Col xs={6} sm={4}>
          <strong>Base</strong>
          <p>{base}</p>
        </Col>
        <Col xs={6} sm={4}>
          <strong>Platoon</strong>
          <p>{platoonContent}</p>
        </Col>
        <Col xs={6}>
          <label>Position</label>
          <Input value={position} onChange={onPositionChanged} />
        </Col>
        <Col xs={12} sm={6}>
          <ButtonBar>
            <Button color='primary' onClick={update}>
              <FontAwesome icon='save' /> Update
            </Button>
            <Button color='danger' onClick={remove}>
              <FontAwesome icon='trash' /> Remove/Delete
            </Button>
            {/* <Button color='primary' id='login' onClick={ this.onLoginChange }>
                <FontAwesome icon='sign-in-alt' /> Login to staff member
              </Button> */}
          </ButtonBar>
        </Col>
      </Row>
    </div>
  );
}

export default PositionRow;
