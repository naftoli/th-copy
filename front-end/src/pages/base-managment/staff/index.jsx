import { Routes, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import { NumericRoute } from 'components/navigation';
import StaffPage from './StaffPage';
import StaffDetailPage from './StaffDetailPage';
// styles for all staff pages
import './staff.scss';

const StaffIndexPage = () => {
  return (
    <Routes>
      <Route index element={<StaffPage />} />
      <Route path=":id" element={
        <NumericRoute fallback={<Page404 />}>
          <StaffDetailPage />
        </NumericRoute>
      } />
      <Route path="*" element={<Page404 />} />
    </Routes>
  )
}

export default StaffIndexPage;
