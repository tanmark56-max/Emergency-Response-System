import React, { useState, useEffect } from 'react';
import {
  Phone,
  Clock,
  RefreshCw,
  CheckCircle,
  AlertTriangle,
  Users,
  Bot,
  Calendar,
  TrendingUp,
  TrendingDown
} from 'lucide-react';
import { useEmergencyCalls } from '../hooks/useEmergencyCalls';
import StatCard from '../components/ui/StatCard';
import PriorityDistribution from '../components/dashboard/PriorityDistribution';
import RecentCallsTable from '../components/dashboard/RecentCallsTable';
import ResponderStatus from '../components/dashboard/ResponderStatus';
import EmergencyTypeChart from '../components/dashboard/EmergencyTypeChart';
import { format, formatDistanceToNow } from 'date-fns';

const Dashboard = () => {
  const { stats, loading, fetchStats } = useEmergencyCalls();
  const [lastUpdated, setLastUpdated] = useState(new Date());

  useEffect(() => {
    fetchStats();
    const interval = setInterval(() => {
      fetchStats();
      setLastUpdated(new Date());
    }, 30000);
    return () => clearInterval(interval);
  }, []);

  const statCards = [
    {
      icon: Phone,
      value: stats.totalCalls || 0,
      label: 'Total Emergency Calls',
      color: 'critical',
      change: `+${stats.todayCalls || 0} today`,
      changeType: 'up'
    },
    {
      icon: Clock,
      value: stats.pending || 0,
      label: 'Pending Response',
      color: 'pending',
      change: stats.pending > 5 ? '⚠️ High backlog' : '✅ Under control',
      changeType: stats.pending > 5 ? 'down' : 'up'
    },
    {
      icon: RefreshCw,
      value: stats.inProgress || 0,
      label: 'In Progress',
      color: 'progress',
      change: '🔄 Active',
      changeType: 'up'
    },
    {
      icon: CheckCircle,
      value: stats.resolved || 0,
      label: 'Resolved',
      color: 'resolved',
      change: '✅ Completed',
      changeType: 'up'
    },
    {
      icon: AlertTriangle,
      value: stats.critical || 0,
      label: 'Critical Priority',
      color: 'critical',
      change: stats.critical > 0 ? '⚠️ Needs immediate action' : '✅ No critical',
      changeType: stats.critical > 0 ? 'down' : 'up'
    },
    {
      icon: Users,
      value: `${stats.availableResponders || 0}/${stats.totalResponders || 0}`,
      label: 'Available Responders',
      color: 'responders',
      change: stats.availableResponders < 3 ? '⚠️ Limited availability' : '✅ Ready',
      changeType: stats.availableResponders < 3 ? 'down' : 'up'
    },
    {
      icon: Bot,
      value: Math.round(stats.avgAIScore || 0),
      label: 'Avg AI Priority Score',
      color: 'ai',
      change: `📊 ${stats.totalAI || 0} analyzed`,
      changeType: 'up'
    },
    {
      icon: Calendar,
      value: stats.todayCalls || 0,
      label: "Today's Calls",
      color: 'today',
      change: stats.todayCalls > 10 ? '📈 High volume' : '📊 Normal',
      changeType: stats.todayCalls > 10 ? 'down' : 'up'
    }
  ];

  if (loading) {
    return (
      <div className="flex items-center justify-center h-[60vh]">
        <div className="flex flex-col items-center gap-3">
          <div className="w-8 h-8 border-4 border-primary border-t-transparent rounded-full animate-spin" />
          <p className="text-muted-foreground">Loading dashboard...</p>
        </div>
      </div>
    );
  }

  return (
    <div className="space-y-6">
      {/* Page Header */}
      <div className="page-header">
        <span className="label">Dashboard</span>
        <h1>📊 Command Center Dashboard</h1>
        <div className="breadcrumb">
          <a href="#">Home</a> / Dashboard
          <span className="ml-4 text-xs text-muted-foreground">
            Last updated: {formatDistanceToNow(lastUpdated, { addSuffix: true })}
          </span>
        </div>
      </div>

      {/* Stats Grid */}
      <div className="content-area">
        <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
          {statCards.map((stat, idx) => (
            <StatCard key={idx} {...stat} />
          ))}
        </div>

        {/* Charts Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
          <PriorityDistribution data={stats.priorityDistribution || {}} />
          <EmergencyTypeChart data={stats.typeDistribution || {}} />
        </div>

        {/* Bottom Section */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
          <div className="lg:col-span-2">
            <RecentCallsTable calls={stats.recentCalls || []} />
          </div>
          <div>
            <ResponderStatus data={stats.responderStatus || {}} />
          </div>
        </div>
      </div>
    </div>
  );
};

export default Dashboard;