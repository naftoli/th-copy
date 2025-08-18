// Expose MultiSelect via window.AppComponents (wrapped to avoid global collisions)
(function() {
  window.AppComponents = window.AppComponents || {};

  const { FormCheck } = ReactBootstrap;
  const { useState, useEffect, useRef } = React;

  window.AppComponents.MultiSelect = function MultiSelect({ options, selectedValues, onChange, placeholder }) {
  const [isOpen, setIsOpen] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');
  const wrapperRef = useRef(null);
  const inputRef = useRef(null);

  const filteredOptions = options.filter(option =>
    option.name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  useEffect(() => {
    const handleClickOutside = (event) => {
      if (wrapperRef.current && !wrapperRef.current.contains(event.target)) {
        setIsOpen(false);
        setSearchTerm('');
      }
    };
    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSelect = (option) => {
    const isSelected = selectedValues.some(s => String(s.value) === String(option.id));
    if (isSelected) {
      onChange(selectedValues.filter(s => String(s.value) !== String(option.id)));
    } else {
      onChange([...selectedValues, { value: option.id, label: option.name }]);
    }
    setSearchTerm('');
  };

  const handleRemove = (valueToRemove) => {
    onChange(selectedValues.filter(s => String(s.value) !== String(valueToRemove)));
  };

  const handleSelectAll = () => {
    if (selectedValues.filter(s => String(s.value) !== '0').length === options.length) {
      const allSchoolsSelected = selectedValues.filter(s => String(s.value) === '0');
      onChange(allSchoolsSelected);
    } else {
      const allSchoolsOption = selectedValues.filter(s => String(s.value) === '0');
      const allIndividualSchools = options.map(option => ({ value: option.id, label: option.name }));
      onChange([...allSchoolsOption, ...allIndividualSchools]);
    }
  };

  return (
    <div className="position-relative dropdown-container" ref={wrapperRef}>
      <div 
        className="form-control d-flex flex-wrap align-items-center"
        style={{ minHeight: '38px', cursor: 'pointer' }}
        onClick={() => setIsOpen(!isOpen)}
        ref={inputRef}
      >
        {selectedValues.length === 0 ? (
          <span className="text-muted">{placeholder}</span>
        ) : (
          <>
            {selectedValues.map((selected) => (
              <span 
                key={selected.value}
                className="badge bg-primary me-1 mb-1"
                style={{ fontSize: '0.8rem' }}
              >
                {selected.label}
                <button
                  type="button"
                  className="btn-close btn-close-white ms-1"
                  style={{ fontSize: '0.6rem' }}
                  onClick={(e) => {
                    e.stopPropagation();
                    handleRemove(selected.value);
                  }}
                />
              </span>
            ))}
          </>
        )}
        <i className={`bi bi-chevron-${isOpen ? 'up' : 'down'} ms-auto`}></i>
      </div>

      {isOpen && (
        <div 
          className="position-fixed bg-white border rounded shadow-sm" 
          style={{ 
            zIndex: 99999, 
            maxHeight: '300px', 
            overflowY: 'auto', 
            width: inputRef.current ? inputRef.current.offsetWidth + 'px' : '100%',
            top: inputRef.current ? inputRef.current.getBoundingClientRect().bottom + 'px' : '0px',
            left: inputRef.current ? inputRef.current.getBoundingClientRect().left + 'px' : '0px'
          }} 
        >
          <div className="p-2 border-bottom">
            <input
              type="text"
              className="form-control form-control-sm"
              placeholder="Search schools..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              onClick={(e) => e.stopPropagation()}
            />
          </div>
          
          <div className="p-2 border-bottom">
            <FormCheck
              type="checkbox"
              checked={selectedValues.filter(s => String(s.value) !== '0').length === options.length && options.length > 0}
              onChange={handleSelectAll}
              label="Select All Schools"
              className="fw-bold"
            />
          </div>
          
          <div>
            <div
              className="px-2 py-1 d-flex align-items-center"
              style={{ cursor: 'pointer' }}
              onClick={() => {
                const isSelected = selectedValues.some(s => s.value === '0');
                if (isSelected) {
                  onChange(selectedValues.filter(s => s.value !== '0'));
                } else {
                  onChange([...selectedValues, { value: '0', label: 'All Schools' }]);
                }
              }}
              onMouseEnter={(e) => e.target.style.backgroundColor = '#f8f9fa'}
              onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
            >
              <FormCheck
                type="checkbox"
                checked={selectedValues.some(s => String(s.value) === '0')}
                onChange={() => {}}
                className="me-2"
              />
              <span className="fw-bold">All Schools</span>
            </div>
            {filteredOptions.map(option => (
              <div
                key={option.id}
                className="px-2 py-1 d-flex align-items-center"
                style={{ cursor: 'pointer' }}
                onClick={() => handleSelect(option)}
                onMouseEnter={(e) => e.target.style.backgroundColor = '#f8f9fa'}
                onMouseLeave={(e) => e.target.style.backgroundColor = 'transparent'}
              >
                <FormCheck
                  type="checkbox"
                  checked={selectedValues.some(s => String(s.value) === String(option.id))}
                  onChange={() => {}}
                  className="me-2"
                />
                <span>{option.name}</span>
              </div>
            ))}
          </div>
        </div>
      )}
    </div>
  );
  };

})();


