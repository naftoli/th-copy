import { Routes, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import { NumericRoute } from 'components/navigation';
import ParentPage from './ParentPage';
import ParentsPage from './ParentsPage';
// styles
import './parents.scss';

const ParentsIndexPage = () => {
  return (
    <Routes>
      <Route index element={<ParentsPage />} />
      <Route path=":id" element={
        <NumericRoute fallback={<Page404 />}>
          <ParentPage />
        </NumericRoute>
      } />
      <Route path="*" element={<Page404 />} />
    </Routes>
  )
}

// const mapStateToProps = ( state ) => ({
//   login: state.login.current_login
// })

export default ParentsIndexPage;
