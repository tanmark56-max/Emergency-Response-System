import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import { AuthProvider } from './contexts/AuthContext';
import Layout from './components/layout/Layout';
import Dashboard from './pages/Dashboard';
import EmergencyIntake from './pages/EmergencyIntake';
import IncidentPrioritization from './pages/IncidentPrioritization';
import DispatchCenter from './pages/DispatchCenter';
import LocationTracking from './pages/LocationTracking';
import StatusMonitoring from './pages/StatusMonitoring';
import CallHistory from './pages/CallHistory';
import Responders from './pages/Responders';
import AIAnalytics from './pages/AIAnalytics';
import Settings from './pages/Settings';
import Login from './pages/Login';

function App() {
  return (
    <AuthProvider>
      <Router>
        <Routes>
          <Route path="/login" element={<Login />} />
          <Route path="/" element={<Layout />}>
            <Route index element={<Navigate to="/dashboard" replace />} />
            <Route path="dashboard" element={<Dashboard />} />
            <Route path="emergency-intake" element={<EmergencyIntake />} />
            <Route path="incident-prioritization" element={<IncidentPrioritization />} />
            <Route path="dispatch-center" element={<DispatchCenter />} />
            <Route path="location-tracking" element={<LocationTracking />} />
            <Route path="status-monitoring" element={<StatusMonitoring />} />
            <Route path="call-history" element={<CallHistory />} />
            <Route path="responders" element={<Responders />} />
            <Route path="ai-analytics" element={<AIAnalytics />} />
            <Route path="settings" element={<Settings />} />
          </Route>
        </Routes>
      </Router>
    </AuthProvider>
  );
}

export default App;