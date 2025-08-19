// Expose PaymentForm via window.AppComponents (wrapped to avoid global const collisions)
(function() {
  window.AppComponents = window.AppComponents || {};

  const { Form, Row, Col, FormSelect, Button, Card, Alert, InputGroup } = ReactBootstrap;

  window.AppComponents.PaymentForm = React.memo(({ 
  paymentForm, 
  setPaymentForm, 
  schools, 
  addingPayment, 
  paymentMessage, 
  isPaymentFormOpen, 
  setIsPaymentFormOpen,
  handlePaymentInputChange,
  handlePaymentSubmit,
  setPaymentMessage,
  paymentTypes,
  paymentMethods
}) => (
  <Card className="mb-4">
    <Card.Header 
      className="accordion-toggle"
      onClick={() => setIsPaymentFormOpen(!isPaymentFormOpen)}
      style={{ cursor: 'pointer' }}
    >
      <h5 className="mb-0 d-flex justify-content-between align-items-center">
        <span>
          <i className={`bi bi-chevron-${isPaymentFormOpen ? 'down' : 'right'}`}></i>
          {' '}Add Payment
        </span>
      </h5>
    </Card.Header>
    {isPaymentFormOpen && (
      <Card.Body>
        <Form onSubmit={handlePaymentSubmit}>
          <Row>
            <Col md={3} className="mb-3">
              <Form.Label htmlFor="school_payment">School:</Form.Label>
              <FormSelect
                id="school_payment"
                value={paymentForm.school}
                onChange={(e) => handlePaymentInputChange('school', e.target.value)}
              >
                <option value="0">Choose School</option>
                {schools.map(school => (
                  <option key={school.id} value={school.id}>{school.name}</option>
                ))}
              </FormSelect>
            </Col>
            <Col md={3} className="mb-3">
              <Form.Label htmlFor="payment_type">Payment Type:</Form.Label>
              <FormSelect
                id="payment_type"
                value={paymentForm.paymentType}
                onChange={(e) => handlePaymentInputChange('paymentType', e.target.value)}
              >
                {paymentTypes.map(type => (
                  <option key={type.value} value={type.value}>{type.label}</option>
                ))}
              </FormSelect>
            </Col>
            <Col md={3} className="mb-3">
              <Form.Label htmlFor="payment_method">Payment Method:</Form.Label>
              <FormSelect
                id="payment_method"
                value={paymentForm.paymentMethod}
                onChange={(e) => handlePaymentInputChange('paymentMethod', e.target.value)}
              >
                {paymentMethods.map(method => (
                  <option key={method.value} value={method.value}>{method.label}</option>
                ))}
              </FormSelect>
            </Col>
            <Col md={3} className="mb-3">
              <Form.Label htmlFor="payment_amount">Amount:</Form.Label>
              <InputGroup>
                <InputGroup.Text>$</InputGroup.Text>
                <Form.Control
                  type="number"
                  id="payment_amount"
                  placeholder="0.00"
                  value={paymentForm.amount}
                  onChange={(e) => handlePaymentInputChange('amount', e.target.value)}
                  min="0"
                />
              </InputGroup>
            </Col>
          </Row>
          <div className="text-center">
            <Button 
              type="submit" 
              variant="primary"
              disabled={addingPayment}
            >
              {addingPayment ? (
                <>
                  <span className="spinner-border spinner-border-sm me-2" role="status"></span>
                  Processing...
                </>
              ) : (
                <>
                  <i className="bi bi-plus-circle"></i> Add Payment
                </>
              )}
            </Button>
          </div>
        </Form>
        
        {paymentMessage && (
          <Alert 
            variant={paymentMessage.type} 
            className="mt-3"
            onClose={() => setPaymentMessage('')}
            dismissible
          >
            {paymentMessage.text}
          </Alert>
        )}
      </Card.Body>
    )}
  </Card>
));

})();
