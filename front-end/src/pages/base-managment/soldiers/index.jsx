import React from 'react';
import { connect } from 'react-redux';
import { Routes, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import { NumericRoute } from 'components/navigation';
import SoldierPage from './SoldierPage/SoldierPage';
import SoldiersPage from './SoldiersPage/SoldiersPage';
import RankCardsPage from './RankCardsPage/RankCardsPage';
import RegistrationPage from './RegistrationPage/RegistrationPage';
// functions
import { isBC } from 'functions/login';

export const SoldiersIndexPage = ({ login }) => {
  const { code } = login;
  const onlyBC = code === 'BC';

  return (
    <Routes>
      <Route index element={<SoldiersPage />} />

      {onlyBC && <Route path="registration" element={<RegistrationPage />} />}
      {isBC(code) && <Route path="cards" element={<RankCardsPage />} />}

      <Route path=":id" element={
        <NumericRoute fallback={<Page404 />}>
          <SoldierPage />
        </NumericRoute>
      } />

      <Route path="*" element={<Page404 />} />
    </Routes>
  )
}

const mapStateToProps = (state) => ({
  login: state.login.current_login
})

export default connect(mapStateToProps)(SoldiersIndexPage);
