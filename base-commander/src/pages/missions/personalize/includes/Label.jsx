import React, { Component, Fragment } from 'react';

const formatGrades = grades => {
  if ( !grades || grades.length === 0 )
    return false;

  if ( grades.length === 1 )
    return grades[0].grade;

  return `(${ grades[0].grade } - ${ grades[ grades.length - 1 ].grade })`;
}

class Label extends Component {

  render() {
    const { label, school_types } = this.props;

    const have_quotas = school_types[0].grades[0].quota;
    
    return (
      <div className='Label'>
        <p className='label-text'>{ label.replace(/[_]{2,}/g, '').trim() }</p>

        <div className='label-grades'>
          <strong>Applies to:</strong>

          { school_types.map( ( { type, grades }, index ) => 
            <p className='label-grade' key={ index }>
              { type } { formatGrades( grades ) }
            </p>
          )}
        </div>
        

        { have_quotas && 
          <div className='label-quotas'>
            <strong>Quotas:</strong>

            { school_types.map( ( { type, grades }, index ) => {

              return (
                <Fragment key={ index }>
                  <p className='label-quota-type'>{ type }</p>
                  <div className='label-quota-grid'>
                    { grades.map( ( { grade, quota }, index ) =>
                      <p className='label-quota' key={ index }>
                        { grade }: <strong>{ quota }</strong>
                      </p>
                    )}
                  </div>
                </Fragment>
              )
            })}

          </div>
        }
      </div>
    )
  }
}

export default Label;
