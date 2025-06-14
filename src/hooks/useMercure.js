import { useEffect, useState, useCallback } from 'react';
import MercureService from '../services/MercureService';

export const useMercure = (topic, dependencies = []) => {
const [data, setData] = useState(null);
const [isConnected, setIsConnected] = useState(false);
const [error, setError] = useState(null);

const handleMessage = useCallback((messageData, messageTopic) => {
setData(messageData);
setError(null);
}, []);

const handleConnectionChange = useCallback(() => {
const state = MercureService.getConnectionState();
setIsConnected(state.isConnected);
}, []);

useEffect(() => {
let unsubscribe;

const initConnection = async () => {
try {
    if (!MercureService.isConnected) {
        await MercureService.connect();
    }
    unsubscribe = MercureService.subscribe(topic, handleMessage);
    
    handleConnectionChange();
} catch (err) {
    setError(err.message);
}
};

initConnection();
return () => {
if (unsubscribe) {
    unsubscribe();
}
};
}, [topic, handleMessage, ...dependencies]);

return {
data,
isConnected,
error,
connectionState: MercureService.getConnectionState()
};
};

export const useMercureWithAuth = (topic, token, dependencies = []) => {
const [data, setData] = useState(null);
const [isConnected, setIsConnected] = useState(false);
const [error, setError] = useState(null);

const handleMessage = useCallback((messageData, messageTopic) => {
setData(messageData);
setError(null);
}, []);

useEffect(() => {
let unsubscribe;

const initConnection = async () => {
try {
    await MercureService.connect(token);

    unsubscribe = MercureService.subscribe(topic, handleMessage);
    
    setIsConnected(true);
} catch (err) {
    setError(err.message);
    setIsConnected(false);
}
};

if (token) {
initConnection();
}

return () => {
if (unsubscribe) {
    unsubscribe();
}
};
}, [topic, token, handleMessage, ...dependencies]);

return { data, isConnected, error };
};