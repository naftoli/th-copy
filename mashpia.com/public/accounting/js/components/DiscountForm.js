// Expose DiscountForm via window.AppComponents (wrapped to avoid global const collisions)
(function() {
  window.AppComponents = window.AppComponents || {};

  const { Form, Row, Col, FormSelect, Button, Card, Alert, InputGroup } = ReactBootstrap;

  window.AppComponents.DiscountForm = React.memo(({ 
  discountForm,
  setDiscountForm,
  schools,
  grades,
  students,
  addingDiscount,
  discountMessage,
  isDiscountFormOpen,
  setIsDiscountFormOpen,
  handleDiscountInputChange,
  handleDiscountTypeChange,
  handleDiscountSchoolChange,
  handleGradeChange,
  handleDiscountSubmit,
  setDiscountMessage
}) => (
  <Card className="mb-4">
    <Card.Header>
      <h6 className="mb-0">Add Voucher</h6>
    </Card.Header>
    <Card.Body>
      <Form onSubmit={handleDiscountSubmit}>
        <Row>
          <Col md={3} className="mb-3">
            <Form.Label>Type</Form.Label>
            <FormSelect
              value={discountForm.discountType}
              onChange={(e) => handleDiscountTypeChange(e.target.value)}
            >
              <option value="base">Base</option>
              <option value="soldier">Soldier</option>
            </FormSelect>
          </Col>
          <Col md={3} className="mb-3">
            <Form.Label>Year</Form.Label>
            <FormSelect
              value={discountForm.year}
              onChange={(e) => handleDiscountInputChange('year', e.target.value)}
            >
              {([discountForm.year].filter(Boolean)).map(y => (
                <option key={y} value={y}>{y}</option>
              ))}
            </FormSelect>
          </Col>
          <Col md={6} className="mb-3">
            <Form.Label>School</Form.Label>
            <FormSelect
              value={discountForm.school}
              onChange={(e) => handleDiscountSchoolChange(e.target.value)}
            >
              <option value="0">Choose School</option>
              {schools.map(s => (
                <option key={s.id} value={s.id}>{s.name}</option>
              ))}
            </FormSelect>
          </Col>

          {discountForm.discountType === 'soldier' && (
            <>
              <Col md={3} className="mb-3">
                <Form.Label>Grade</Form.Label>
                <FormSelect
                  value={discountForm.grade}
                  onChange={(e) => handleGradeChange(e.target.value)}
                >
                  <option value="">Choose Grade</option>
                  {grades.map(g => (
                    <option key={g} value={g}>{g}</option>
                  ))}
                </FormSelect>
              </Col>
              <Col md={9} className="mb-3">
                <Form.Label>Student Serial</Form.Label>
                <Form.Control
                  type="text"
                  value={discountForm.student}
                  onChange={(e) => handleDiscountInputChange('student', e.target.value)}
                  placeholder="Enter student serial"
                />
              </Col>
            </>
          )}

          <Col md={4} className="mb-3">
            <Form.Label>Amount</Form.Label>
            <InputGroup>
              <InputGroup.Text>$</InputGroup.Text>
              <Form.Control
                type="text"
                value={discountForm.amount}
                onChange={(e) => handleDiscountInputChange('amount', e.target.value)}
                placeholder="0.00"
              />
            </InputGroup>
          </Col>
          <Col md={4} className="mb-3">
            <Form.Label>Reason</Form.Label>
            <Form.Control
              type="text"
              value={discountForm.reason}
              onChange={(e) => handleDiscountInputChange('reason', e.target.value)}
              placeholder="Reason for voucher"
            />
          </Col>
          <Col md={4} className="mb-3">
            <Form.Label>Created By</Form.Label>
            <Form.Control
              type="text"
              value={discountForm.created_by}
              onChange={(e) => handleDiscountInputChange('created_by', e.target.value)}
              placeholder="Your name"
            />
          </Col>
        </Row>
        <div className="text-center">
          <Button type="submit" variant="primary" disabled={addingDiscount}>
            {addingDiscount ? (
              <>
                <span className="spinner-border spinner-border-sm me-2" role="status"></span>
                Processing...
              </>
            ) : (
              <>Create Voucher</>
            )}
          </Button>
        </div>
      </Form>

      {discountMessage && (
        <Alert 
          variant={discountMessage.type} 
          className="mt-3"
          onClose={() => setDiscountMessage('')}
          dismissible
        >
          {discountMessage.text}
        </Alert>
      )}
    </Card.Body>
  </Card>
));

})();
