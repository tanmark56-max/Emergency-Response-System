import React, { useState, useEffect } from 'react';
import { NavLink, useNavigate } from 'react-router-dom';
import {
  Home,
  Phone,
  Robot,
  TowerBroadcast,
  MapPin,
  HeartPulse,
  History,
  Users,
  ChartLine,
  Settings,
  LogOut,
  Shield,
  ChevronDown,
  Menu,
  X
} from 'lucide-react';
import { useAuth } from '../../hooks/useAuth';

const Sidebar = ({ isOpen, setIsOpen, isMobile }) => {
  const { user, logout } = useAuth();
  const navigate = useNavigate();
  const [openDropdowns, setOpenDropdowns] = useState({});

  const toggleDropdown = (key) => {
    setOpenDropdowns(prev => ({ ...prev, [key]: !prev[key] }));
  };

  const navItems = [
    {
      section: 'Main',
      items: [
        { path: '/dashboard', icon: Home, label: 'Dashboard' }
      ]
    },
    {
      section: 'Call Management',
      items: [
        { path: '/emergency-intake', icon: Phone, label: 'Emergency Call Intake', badge: 'pending' },
        { path: '/call-history', icon: History, label: 'Call History' }
      ]
    },
    {
      section: 'AI & Analytics',
      items: [
        { path: '/incident-prioritization', icon: Robot, label: 'Incident Prioritization', badge: 'AI' },
        { path: '/ai-analytics', icon: ChartLine, label: 'AI Analytics' }
      ]
    },
    {
      section: 'Operations',
      items: [
        { path: '/dispatch-center', icon: TowerBroadcast, label: 'Response Team Dispatch' },
        { path: '/responders', icon: Users, label: 'Responders Management' }
      ]
    },
    {
      section: 'Location & Monitoring',
      items: [
        { path: '/location-tracking', icon: MapPin, label: 'Real-Time Location', badge: 'Live' },
        { path: '/status-monitoring', icon: HeartPulse, label: 'Status Monitoring', badge: 'Active' }
      ]
    },
    {
      section: 'System',
      items: [
        { path: '/settings', icon: Settings, label: 'Settings' }
      ]
    }
  ];

  const handleLogout = () => {
    logout();
    navigate('/login');
  };

  return (
    <>
      {/* Mobile Overlay */}
      {isMobile && isOpen && (
        <div 
          className="fixed inset-0 bg-black/50 z-40"
          onClick={() => setIsOpen(false)}
        />
      )}

      <aside 
        className={`fixed top-0 left-0 h-full w-[280px] bg-card border-r border-border 
                    z-50 transition-transform duration-300 flex flex-col
                    ${isOpen ? 'translate-x-0' : '-translate-x-full'}`}
      >
        {/* Brand */}
        <div className="p-5 border-b border-border flex-shrink-0">
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                <Shield className="w-6 h-6 text-primary" />
              </div>
              <div>
                <h2 className="text-lg font-bold font-display leading-tight">Barangay 178</h2>
                <p className="text-xs text-muted-foreground">Emergency Response</p>
              </div>
            </div>
            {isMobile && (
              <button onClick={() => setIsOpen(false)} className="p-1 hover:bg-secondary rounded-lg">
                <X className="w-5 h-5" />
              </button>
            )}
          </div>
          <div className="mt-1">
            <span className="text-[10px] font-semibold uppercase tracking-wider text-primary/70 bg-primary/10 px-2.5 py-0.5 rounded-full">
              Camarin North • Caloocan
            </span>
          </div>
        </div>

        {/* Navigation */}
        <nav className="flex-1 overflow-y-auto p-3 space-y-4">
          {navItems.map((section, idx) => (
            <div key={idx}>
              <p className="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground/70 px-3 mb-2">
                {section.section}
              </p>
              <div className="space-y-0.5">
                {section.items.map((item) => (
                  <NavLink
                    key={item.path}
                    to={item.path}
                    className={({ isActive }) =>
                      `sidebar-link ${isActive ? 'active' : ''}`
                    }
                    onClick={() => isMobile && setIsOpen(false)}
                  >
                    <item.icon className="icon" />
                    <span>{item.label}</span>
                    {item.badge && (
                      <span className={`badge ${
                        item.badge === 'pending' ? 'badge-pending' :
                        item.badge === 'Live' ? 'badge-success' :
                        item.badge === 'Active' ? 'badge-critical' :
                        'badge-pending'
                      }`}>
                        {item.badge}
                      </span>
                    )}
                  </NavLink>
                ))}
              </div>
            </div>
          ))}
        </nav>

        {/* Footer */}
        <div className="p-4 border-t border-border flex-shrink-0">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
              <span className="text-sm font-bold text-primary">
                {user?.fullName?.charAt(0) || 'U'}
              </span>
            </div>
            <div className="flex-1 min-w-0">
              <p className="text-sm font-medium truncate">{user?.fullName || 'User'}</p>
              <p className="text-xs text-muted-foreground capitalize">{user?.role || 'User'}</p>
            </div>
          </div>
          <button
            onClick={handleLogout}
            className="w-full flex items-center gap-2 px-3 py-2 text-sm text-muted-foreground hover:text-destructive hover:bg-destructive/10 rounded-lg transition-all"
          >
            <LogOut className="w-4 h-4" />
            Logout
          </button>
        </div>
      </aside>
    </>
  );
};

export default Sidebar;