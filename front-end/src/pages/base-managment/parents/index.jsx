import { Routes, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import ParentPage from './ParentPage';
import ParentsPage from './ParentsPage';
// styles
import './parents.scss';

const ParentsIndexPage = () => {
  return (
    <Routes>
      <Route index element={<ParentsPage />} />
      <Route path=":id" element={<ParentPage />} />
      <Route path="*" element={<Page404 />} />
    </Routes>
  )
}

// const mapStateToProps = ( state ) => ({
//   login: state.login.current_login
// })

export default ParentsIndexPage;
