import React from 'react';

const StatusBadge = ({ status }) => {
  const statusMap = {
    'Pending': 'status-badge-pending',
    'Dispatched': 'status-badge-dispatched',
    'In-Progress': 'status-badge-in-progress',
    'Resolved': 'status-badge-resolved',
    'Closed': 'status-badge-closed'
  };

  return (
    <span className={`status-badge ${statusMap[status] || 'status-badge-pending'}`}>
      {status}
    </span>
  );
};

export default StatusBadge;