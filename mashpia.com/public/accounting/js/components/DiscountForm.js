// Expose DiscountForm via window.AppComponents (wrapped to avoid global const collisions)
(function() {
    window.AppComponents = window.AppComponents || {};

    const { Form, Row, Col, FormSelect, Button, Card, Alert, InputGroup } = ReactBootstrap;

    window.AppComponents.DiscountForm = React.memo(({ 
        discountForm,
        setDiscountForm,
        schools,
        students,
        addingDiscount,
        discountMessage,
        handleDiscountInputChange,
        handleDiscountTypeChange,
        handleDiscountSchoolChange,
        handleGradeChange,
        handleDiscountSubmit,
        setDiscountMessage,
        formData,
        availableGrades
    }) => (
        <Card className="mb-4">
        <Card.Body>
            <h5 className="card-title">Add Voucher</h5>
            <Form onSubmit={handleDiscountSubmit}>
            <Row>
                {/* Type and Year - Always visible */}
                <Col md={3} className="mb-3">
                <Form.Label>Type</Form.Label>
                <FormSelect
                    id="discount_type"
                    name="discountType"
                    value={discountForm.discountType}
                    onChange={(e) => handleDiscountTypeChange(e.target.value)}
                >
                    {formData.reportType === 'base_discounts' && (
                    <option value="base">Base</option>
                    )}
                    {formData.reportType === 'soldier_discounts' && (
                    <option value="soldier">Soldier</option>
                    )}
                </FormSelect>
                </Col>
                <Col md={3} className="mb-3">
                <Form.Label>Year</Form.Label>
                <FormSelect
                    id="discount_year"
                    name="year"
                    value={discountForm.year}
                    onChange={(e) => handleDiscountInputChange('year', e.target.value)}
                >
                    {([discountForm.year || formData.year].filter(Boolean)).map(y => (
                    <option key={y} value={y}>{y}</option>
                    ))}
                </FormSelect>
                </Col>
                <Col md={6} className="mb-3">
                <Form.Label>School</Form.Label>
                <FormSelect
                    id="discount_school"
                    name="school"
                    value={discountForm.school}
                    onChange={(e) => handleDiscountSchoolChange(e.target.value)}
                >
                    <option value="0">Choose School</option>
                    {schools.map(s => (
                    <option key={s.id} value={s.id}>{s.name}</option>
                    ))}
                </FormSelect>
                </Col>

                {/* Grade and Student - Only for soldier discounts */}
                {discountForm.discountType === 'soldier' && (
                <>
                                     <Col md={3} className="mb-3">
                 <Form.Label>Grade</Form.Label>
                 <FormSelect
                     id="discount_grade"
                     name="grade"
                     value={discountForm.grade}
                     onChange={(e) => handleGradeChange(e.target.value)}
                     disabled={!discountForm.school || availableGrades.length === 0}
                 >
                     <option value="">Choose Grade</option>
                     {availableGrades.map(grade => (
                     <option key={grade.class_id} value={grade.class_id}>
                         {grade.class_grade}{grade.class_sub ? ` ${grade.class_sub}` : ''}
                     </option>
                     ))}
                 </FormSelect>
                 </Col>
                    <Col md={9} className="mb-3">
                    <Form.Label>Student</Form.Label>
                    <FormSelect
                        id="discount_student"
                        name="student"
                        value={discountForm.student}
                        onChange={(e) => handleDiscountInputChange('student', e.target.value)}
                        disabled={!discountForm.grade || students.length === 0}
                    >
                        <option value="">Choose Student</option>
                        {students.map(student => (
                        <option key={student.user_id} value={student.user_id}>
                            {student.last}, {student.first}
                        </option>
                        ))}
                    </FormSelect>
                    </Col>
                </>
                )}

                {/* Amount, Reason, Created By - Always visible */}
                <Col md={4} className="mb-3">
                <Form.Label>Amount</Form.Label>
                <InputGroup>
                    <InputGroup.Text>$</InputGroup.Text>
                    <Form.Control
                    id="discount_amount"
                    name="amount"
                    type="number"
                    value={discountForm.amount}
                    onChange={(e) => handleDiscountInputChange('amount', e.target.value)}
                    placeholder="0.00"
                    step="0.01"
                    min="0"
                    />
                </InputGroup>
                </Col>
                <Col md={4} className="mb-3">
                <Form.Label>Reason</Form.Label>
                <Form.Control
                    id="discount_reason"
                    name="reason"
                    type="text"
                    value={discountForm.reason}
                    onChange={(e) => handleDiscountInputChange('reason', e.target.value)}
                    placeholder="Reason for voucher"
                />
                </Col>
                <Col md={4} className="mb-3">
                <Form.Label>Created By</Form.Label>
                <Form.Control
                    id="discount_created_by"
                    name="created_by"
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
