import React from 'react';
import { Eye } from 'lucide-react';
import StatusBadge from '../ui/StatusBadge';
import PriorityBadge from '../ui/PriorityBadge';
import EmergencyTypeBadge from '../ui/EmergencyTypeBadge';
import { format } from 'date-fns';

const RecentCallsTable = ({ calls }) => {
  if (!calls || calls.length === 0) {
    return (
      <div className="chart-card">
        <h3>📋 Recent Emergency Calls</h3>
        <div className="text-center py-8 text-muted-foreground">
          No recent calls found.
        </div>
      </div>
    );
  }

  return (
    <div className="chart-card">
      <h3>📋 Recent Emergency Calls</h3>
      <div className="table-container">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Type</th>
              <th>Location</th>
              <th>Priority</th>
              <th>Status</th>
              <th>Time</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            {calls.map((call) => (
              <tr key={call.id}>
                <td className="font-medium">#{call.id}</td>
                <td>
                  <EmergencyTypeBadge type={call.emergencyType} />
                </td>
                <td className="max-w-[150px] truncate">
                  {call.location}
                </td>
                <td>
                  <PriorityBadge priority={call.priority} />
                </td>
                <td>
                  <StatusBadge status={call.status} />
                </td>
                <td className="text-sm text-muted-foreground">
                  {format(new Date(call.createdAt), 'MM/dd HH:mm')}
                </td>
                <td>
                  <button className="p-1.5 hover:bg-secondary rounded-lg transition-all">
                    <Eye className="w-4 h-4 text-muted-foreground" />
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

export default RecentCallsTable;    