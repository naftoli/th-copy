import { Routes, Route } from 'react-router-dom';
// sub pages
import BasePage from './BasePage';
import BasesPage from './BasesPage';
import { Page404 } from 'pages/errors';
import { NumericRoute } from 'components/navigation';
// styles for all base pages
import './includes/base.scss';

const BaseIndexPage = () => {
  return (
    <Routes>
      {/* Index routes */}
      <Route index element={<BasesPage />} />
      {/* Detail Page Routes */}
      <Route path=":id" element={
        <NumericRoute fallback={<Page404 />}>
          <BasePage />
        </NumericRoute>
      } />
      {/* All other routes */}
      <Route path="*" element={<Page404 />} />
    </Routes>
  )
}


export default BaseIndexPage;
