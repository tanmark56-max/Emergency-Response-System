import React from 'react';
import { TrendingUp } from 'lucide-react';

const PriorityDistribution = ({ data }) => {
  const priorities = ['Critical', 'High', 'Medium', 'Low'];
  const maxValue = Math.max(...priorities.map(p => data[p] || 0), 1);

  const barColors = {
    'Critical': 'bar-critical',
    'High': 'bar-high',
    'Medium': 'bar-medium',
    'Low': 'bar-low'
  };

  return (
    <div className="chart-card">
      <h3>
        <TrendingUp className="w-5 h-5 text-primary" />
        Priority Distribution
      </h3>
      <div className="space-y-3">
        {priorities.map((priority) => {
          const value = data[priority] || 0;
          const percentage = (value / maxValue) * 100;
          return (
            <div key={priority} className="priority-bar">
              <span className="bar-label">{priority}</span>
              <div className="bar-track">
                <div
                  className={`bar-fill ${barColors[priority]}`}
                  style={{ width: `${Math.max(percentage, 5)}%` }}
                >
                  {value > 0 && value}
                </div>
              </div>
            </div>
          );
        })}
      </div>
    </div>
  );
};

export default PriorityDistribution;