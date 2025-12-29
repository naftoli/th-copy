import React, { useEffect } from 'react';
import { NumberDisplay } from 'components/ui';
import { Select } from 'components/inputs';
import { SaveButton } from 'components/buttons';
import { Row, Col, Label, Input, Button, FormFeedback } from 'reactstrap';

export const OrderForm = (props) => {
  let { items, store, saving, addItem, updateItem, removeItem } = props;

  // Initialize first item if empty
  useEffect(() => {
    // getOptions calculation for check
    const prizeOptions = store.prizes.map(prize => ({
      ...prize,
      value: prize.prize_id,
      isDisabled: prize.points > store.miles
    }));

    if ((!items || items.length === 0) && prizeOptions.length > 0) {
      addItem();
    }
  }, []); // Run once on mount

  const getOptions = () => {
    return store.prizes.map(prize => ({
      ...prize,
      value: prize.prize_id,
      label: `${prize.prize_name} (${prize.points.toLocaleString(navigator.language)} Miles)`,
      isDisabled: prize.points > store.miles
    }));
  };

  const prizeOptions = getOptions();

  // Calculate totals
  const total = items ? items.reduce((sum, item) => {
    return sum + (item.prize && item.qty ? item.prize.points * item.qty : 0);
  }, 0) : 0;

  const exceedsMiles = total > store.miles;

  // Validation Check
  const hasInvalidItems = items ? items.some(item => {
    if (!item.prize) return true;

    let max = 100;
    if (item.prize) {
      max = Math.floor(store.miles / item.prize.points);
      if (max > item.prize.prize_count) max = item.prize.prize_count;
      if (item.prize.one_per_user) max = 1;
    }

    return item.qty > max || (item.qty > 1 && !!item.prize.one_per_user);
  }) : true;

  const disabled = !items || items.length === 0 || hasInvalidItems || exceedsMiles;

  return (
    <div id='OrderForm'>
      {items && items.map((item, index) => {
        let max = 100;
        if (item.prize) {
          max = Math.floor(store.miles / item.prize.points);
          if (max > item.prize.prize_count) max = item.prize.prize_count;
          if (item.prize.one_per_user) max = 1;
        }

        const one_per_user_invalid = item.qty > 1 && !!(item.prize && item.prize.one_per_user);
        const qtyInvalid = item.qty > max || item.qty < 1 || one_per_user_invalid;

        return (
          <div key={index} className="mb-3">
            <Row>
              <Col xs={9} sm={8}>
                <Label>Prize {index + 1}</Label>
                <Select
                  value={item.prize}
                  options={prizeOptions}
                  openMenuOnFocus={false}
                  onChange={(prize) => updateItem(index, { ...item, prize })}
                />
              </Col>

              <Col xs={3} sm={4}>
                <Label>Qty</Label>
                <Input
                  type='number'
                  min={1}
                  max={max}
                  required
                  invalid={qtyInvalid}
                  value={item.qty}
                  onChange={(e) => updateItem(index, { ...item, qty: parseInt(e.target.value, 10) || 1 })}
                />
                {/* Logic: If one_per_user_invalid, show standard error.
                     If generic invalid (qty > max), show range. */}
                {one_per_user_invalid
                  ? <FormFeedback>1 per soldier</FormFeedback>
                  : <FormFeedback>1 - <NumberDisplay value={max} /></FormFeedback>
                }
              </Col>
            </Row>

            {items.length > 1 && (
              <Row>
                <Col xs={12} className="text-end">
                  <Button
                    color="danger"
                    size="sm"
                    onClick={() => removeItem(index)}
                    className="mt-2"
                  >
                    Remove Item
                  </Button>
                </Col>
              </Row>
            )}
          </div>
        );
      })}

      <Row className="mb-3">
        <Col xs={12} className="text-center">
          <Button
            color="outline-primary"
            size="sm"
            onClick={addItem}
          >
            Add Another Item
          </Button>
        </Col>
      </Row>

      {exceedsMiles && (
        <Row className="mb-3">
          <Col xs={12}>
            <div className="alert alert-danger">
              <strong>Insufficient Miles:</strong> Total order cost ({total.toLocaleString()} miles) exceeds available miles ({store.miles.toLocaleString()} miles).
              Please reduce quantities or remove items.
            </div>
          </Col>
        </Row>
      )}

      <Row id='total-row'>
        <Col xs={6} sm={4}>
          <Label>Soldier's Miles</Label>
          <NumberDisplay className='total' value={store.miles} />
        </Col>

        <Col xs={6} sm={4}>
          <Label>Final Price</Label>
          <NumberDisplay className='total' value={total} />
        </Col>

        <Col xs={12} sm={4}>
          <SaveButton
            show={true}
            saving={saving}
            text='Place Order'
            disabled={disabled} />
        </Col>
      </Row>
    </div>
  );
};
