import { Routes, Route } from 'react-router-dom';
// sub pages
import { Page404 } from 'pages/errors';
import TasksPage from './tasks/TasksPage';
import CardsPage from './cards/CardsPage';
import MilesPage from './miles/MilesPage';
import OrdersPage from './orders/OrdersPage';
import PrizesPage from './prizes/PrizesPage';
import TemplatesPage from './prizes/TemplatesPage';

export const BaseManagmentIndexPage = () => {
  return (
    <Routes>
      <Route path="cards" element={<CardsPage />} />
      <Route path="tasks" element={<TasksPage />} />

      <Route path="orders" element={<OrdersPage />} />

      <Route path="prizes" element={<PrizesPage />} />
      <Route path="templates" element={<TemplatesPage />} />

      <Route path="miles" element={<MilesPage />} />

      <Route path="*" element={<Page404 />} />
    </Routes>
  )
}

export default BaseManagmentIndexPage;
