import { Routes, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import { NumericRoute } from 'components/navigation';
import PlatoonPage from './PlatoonPage';
import PlatoonsPage from './PlatoonsPage';
import PlatoonTransitionPage from './PlatoonTransitionPage/PlatoonTransitionPage';
// functions
import { connect } from 'react-redux';
// styles
import './platoons.scss';

export const PlatoonsIndexPage = () => {
  return (
    <Routes>
      <Route index element={<PlatoonsPage />} />
      <Route path="transition" element={<PlatoonTransitionPage />} />
      <Route path=":id" element={
        <NumericRoute fallback={<Page404 />}>
          <PlatoonPage />
        </NumericRoute>
      } />
      <Route path="*" element={<Page404 />} />
    </Routes>
  )
}

const mapStateToProps = (state) => ({
  login: state.login.current_login
})

export default connect(mapStateToProps)(PlatoonsIndexPage);
