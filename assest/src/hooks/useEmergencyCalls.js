import { useState, useCallback } from 'react';
import axios from 'axios';

export const useEmergencyCalls = () => {
  const [stats, setStats] = useState({
    totalCalls: 0,
    pending: 0,
    inProgress: 0,
    resolved: 0,
    critical: 0,
    todayCalls: 0,
    availableResponders: 0,
    totalResponders: 0,
    avgAIScore: 0,
    totalAI: 0,
    priorityDistribution: {},
    typeDistribution: {},
    responderStatus: {},
    recentCalls: []
  });
  const [loading, setLoading] = useState(true);

  const fetchStats = useCallback(async () => {
    try {
      setLoading(true);
      const response = await axios.get('/api/dashboard/stats');
      setStats(response.data);
    } catch (error) {
      console.error('Error fetching stats:', error);
    } finally {
      setLoading(false);
    }
  }, []);

  const createCall = useCallback(async (data) => {
    try {
      const response = await axios.post('/api/emergency/calls', data);
      return response.data;
    } catch (error) {
      throw error;
    }
  }, []);

  const getCall = useCallback(async (id) => {
    try {
      const response = await axios.get(`/api/emergency/calls/${id}`);
      return response.data;
    } catch (error) {
      throw error;
    }
  }, []);

  const updateCall = useCallback(async (id, data) => {
    try {
      const response = await axios.put(`/api/emergency/calls/${id}`, data);
      return response.data;
    } catch (error) {
      throw error;
    }
  }, []);

  return { stats, loading, fetchStats, createCall, getCall, updateCall };
};