import React from 'react';

const EmergencyTypeBadge = ({ type }) => {
  const typeMap = {
    'Fire': 'type-badge-fire',
    'Medical': 'type-badge-medical',
    'Crime': 'type-badge-crime',
    'Accident': 'type-badge-accident',
    'Natural Disaster': 'type-badge-natural-disaster',
    'Flood': 'type-badge-flood',
    'Other': 'type-badge-other'
  };

  const icons = {
    'Fire': '🔥',
    'Medical': '🚑',
    'Crime': '🚨',
    'Accident': '🚗',
    'Natural Disaster': '🌊',
    'Flood': '🌊',
    'Other': '📌'
  };

  return (
    <span className={`type-badge ${typeMap[type] || 'type-badge-other'}`}>
      {icons[type]} {type}
    </span>
  );
};

export default EmergencyTypeBadge;