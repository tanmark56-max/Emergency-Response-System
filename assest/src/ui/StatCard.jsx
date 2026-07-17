import React from 'react';

const StatCard = ({ icon: Icon, value, label, color, change, changeType }) => {
  const colorMap = {
    critical: 'border-t-destructive',
    pending: 'border-t-orange-500',
    progress: 'border-t-blue-500',
    resolved: 'border-t-green-500',
    responders: 'border-t-purple-500',
    ai: 'border-t-cyan-500',
    today: 'border-t-pink-500'
  };

  const numberColorMap = {
    critical: 'text-destructive',
    pending: 'text-orange-500',
    progress: 'text-blue-500',
    resolved: 'text-green-500',
    responders: 'text-purple-500',
    ai: 'text-cyan-500',
    today: 'text-pink-500'
  };

  return (
    <div className={`stat-card ${colorMap[color] || ''}`}>
      {Icon && <Icon className="stat-icon text-muted-foreground" />}
      <span className={`stat-number ${numberColorMap[color] || ''}`}>{value}</span>
      <span className="stat-label">{label}</span>
      {change && (
        <span className={`stat-change ${changeType === 'up' ? 'stat-change-up' : 'stat-change-down'}`}>
          {change}
        </span>
      )}
    </div>
  );
};

export default StatCard;