import React from 'react';
import { LEGACY_URL } from 'components/constants';
import { Row, Col } from 'reactstrap';
import { DateDisplay } from 'components/ui';

const RankBoard = ({ board }) => {
  return (
    <div className='RankBoard'>
      { board.ranks.map( (rank, index) => 
        <RankRow rank={ rank } key={ index }/>
      )}
    </div>
  );
}

const RankRow = ( { rank } ) => {
  // create an array of empty slots
  const medals_required = parseInt(rank.medals_required, 10);
  const unfilled = rank.total_medals - ( rank.medals.length + parseInt(rank.medals_required, 10) );
  const unfilled_slots = new Array( unfilled ).fill('').map( (x, index) =>
    <div key={index}>
      <img src={ `${LEGACY_URL}/kiosk/images/medals/holder.png` } alt='medal' style={{opacity: '.3'}}/>
      <p>(#{ medals_required + rank.medals.length + index + 1 })</p>
    </div>
  );

  return (
    <Row className='RankRow'>
      <Col xs='3' sm='2' className='RankRow-rank'>
        <p>{ rank.rank_name }</p>
        <img src={`${LEGACY_URL}/mobile/img_new/ranks/${rank.rank_ord}.svg`} alt='rank' />
        <p>(<DateDisplay value={ rank.date_promoted } />)</p>
      </Col>
      <Col xs='9' sm='10' className='RankRow-medals'>
        { rank.medals && rank.medals.map( (medal, index) => 
          <div key={index} className='dates'>
            <img src={ `${LEGACY_URL}${medal.photo}` } alt='medal'/>
            <p className='english-date'>
              <DateDisplay value={ medal.date_awarded } />
            </p>
            <p className='hebrew-date'>
              { medal.date_awarded_he }
            </p>
          </div>
        ) }

        { unfilled_slots }
        
      </Col>
    </Row>
  )
}

export default RankBoard;
