import React from 'react';

const PriorityBadge = ({ priority }) => {
  const priorityMap = {
    'Critical': 'priority-critical',
    'High': 'priority-high',
    'Medium': 'priority-medium',
    'Low': 'priority-low'
  };

  return (
    <span className={`priority-badge ${priorityMap[priority] || 'priority-medium'}`}>
      {priority}
    </span>
  );
};

export default PriorityBadge;