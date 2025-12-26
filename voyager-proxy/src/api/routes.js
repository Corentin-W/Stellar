import express from 'express';
import logger from '../utils/logger.js';
import roboTargetRoutes from './robotarget/routes.js';

const router = express.Router();

// Dashboard State
router.get('/dashboard/state', async (req, res, next) => {
  try {
    const state = req.voyager.getState();

    if (!state.controlData) {
      return res.json({
        success: false,
        connected: state.isConnected,
        authenticated: state.isAuthenticated,
        message: 'No control data available yet. Enable Dashboard mode first.',
        data: null,
      });
    }

    res.json({
      success: true,
      timestamp: new Date().toISOString(),
      data: state.controlData,
    });
  } catch (error) {
    next(error);
  }
});

// Connection Status
router.get('/status/connection', (req, res) => {
  const state = req.voyager.getState();

  res.json({
    success: true,
    connection: state.connection,
    isConnected: state.isConnected,
    isAuthenticated: state.isAuthenticated,
    version: state.version,
    sessionKey: req.voyager.sessionKey,
  });
});

// Enable Dashboard Mode
router.post('/dashboard/enable', async (req, res, next) => {
  try {
    await req.voyager.commands.setDashboardMode(true);

    res.json({
      success: true,
      message: 'Dashboard mode enabled',
    });
  } catch (error) {
    next(error);
  }
});

// Control - Abort
router.post('/control/abort', async (req, res, next) => {
  try {
    logger.info('Abort command received');

    const result = await req.voyager.commands.abort();

    res.json({
      success: true,
      message: 'Abort command sent',
      result,
    });
  } catch (error) {
    next(error);
  }
});

// Control - Toggle Target
router.post('/control/toggle', async (req, res, next) => {
  try {
    const { targetGuid, activate } = req.body;

    if (!targetGuid) {
      return res.status(400).json({
        success: false,
        error: 'targetGuid is required',
      });
    }

    logger.info(`${activate ? 'Activating' : 'Deactivating'} target: ${targetGuid}`);

    const result = activate
      ? await req.voyager.commands.activateTarget(targetGuid)
      : await req.voyager.commands.deactivateTarget(targetGuid);

    res.json({
      success: true,
      message: `Target ${activate ? 'activated' : 'deactivated'}`,
      result,
    });
  } catch (error) {
    next(error);
  }
});

// Camera Preview
router.get('/camera/preview', (req, res) => {
  const state = req.voyager.getState();

  // Return latest JPG preview if available
  // In a real implementation, you'd cache the latest newJPG event

  res.json({
    success: true,
    message: 'Preview endpoint - use WebSocket for real-time previews',
    note: 'Subscribe to "newJPG" event via WebSocket for Base64 image data',
  });
});

// RoboTarget - Sets
router.post('/robotarget/sets', async (req, res, next) => {
  try {
    const setData = req.body;
    const result = await req.voyager.commands.addSet(setData);

    res.json({
      success: true,
      message: 'Set created',
      result,
    });
  } catch (error) {
    next(error);
  }
});

router.put('/robotarget/sets/:guid', async (req, res, next) => {
  try {
    const setData = { ...req.body, Guid: req.params.guid };
    const result = await req.voyager.commands.updateSet(setData);

    res.json({
      success: true,
      message: 'Set updated',
      result,
    });
  } catch (error) {
    next(error);
  }
});

router.delete('/robotarget/sets/:guid', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.deleteSet(req.params.guid);

    res.json({
      success: true,
      message: 'Set deleted',
      result,
    });
  } catch (error) {
    next(error);
  }
});

router.get('/robotarget/sets', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.listSets();

    // Extract the list from the result (ParamRet.list or parsed.params.list)
    const sets = result.ParamRet?.list || result.parsed?.params?.list || [];

    res.json({
      success: true,
      sets: sets.map(set => ({
        GuidSet: set.guid,
        SetName: set.setname,
        ProfileName: set.profilename,
        IsDefault: set.isdefault,
        Status: set.status,
        Tag: set.tag,
        Note: set.note
      })),
    });
  } catch (error) {
    next(error);
  }
});

// RoboTarget - Targets
router.post('/robotarget/targets', async (req, res, next) => {
  try {
    const targetData = req.body;
    const result = await req.voyager.commands.addTarget(targetData);

    res.json({
      success: true,
      message: 'Target created',
      result,
    });
  } catch (error) {
    next(error);
  }
});

router.put('/robotarget/targets/:guid', async (req, res, next) => {
  try {
    const targetData = { ...req.body, GuidTarget: req.params.guid };
    const result = await req.voyager.commands.updateTarget(targetData);

    res.json({
      success: true,
      message: 'Target updated',
      result,
    });
  } catch (error) {
    next(error);
  }
});

router.delete('/robotarget/targets/:guid', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.deleteTarget(req.params.guid);

    res.json({
      success: true,
      message: 'Target deleted',
      result,
    });
  } catch (error) {
    next(error);
  }
});

router.post('/robotarget/targets/:guid/activate', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.activateTarget(req.params.guid);

    res.json({
      success: true,
      message: 'Target activated',
      result,
    });
  } catch (error) {
    next(error);
  }
});

router.post('/robotarget/targets/:guid/deactivate', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.deactivateTarget(req.params.guid);

    res.json({
      success: true,
      message: 'Target deactivated',
      result,
    });
  } catch (error) {
    next(error);
  }
});

router.get('/robotarget/targets', async (req, res, next) => {
  try {
    const { setGuid } = req.query;

    if (!setGuid) {
      return res.status(400).json({
        success: false,
        error: 'setGuid query parameter is required',
      });
    }

    const result = await req.voyager.commands.listTargetsForSet(setGuid);

    res.json({
      success: true,
      result,
    });
  } catch (error) {
    next(error);
  }
});

// RoboTarget - Shots
router.post('/robotarget/shots', async (req, res, next) => {
  try {
    const shotData = req.body;
    const result = await req.voyager.commands.addShot(shotData);

    res.json({
      success: true,
      message: 'Shot created',
      result,
    });
  } catch (error) {
    next(error);
  }
});

router.put('/robotarget/shots/:guid', async (req, res, next) => {
  try {
    const shotData = { ...req.body, GuidShot: req.params.guid };
    const result = await req.voyager.commands.updateShot(shotData);

    res.json({
      success: true,
      message: 'Shot updated',
      result,
    });
  } catch (error) {
    next(error);
  }
});

router.delete('/robotarget/shots/:guid', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.deleteShot(req.params.guid);

    res.json({
      success: true,
      message: 'Shot deleted',
      result,
    });
  } catch (error) {
    next(error);
  }
});

// Telescope Control
router.post('/telescope/park', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.park();
    res.json({ success: true, result });
  } catch (error) {
    next(error);
  }
});

router.post('/telescope/unpark', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.unpark();
    res.json({ success: true, result });
  } catch (error) {
    next(error);
  }
});

router.post('/telescope/tracking/start', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.startTracking();
    res.json({ success: true, result });
  } catch (error) {
    next(error);
  }
});

router.post('/telescope/tracking/stop', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.stopTracking();
    res.json({ success: true, result });
  } catch (error) {
    next(error);
  }
});

// Camera Control
router.post('/camera/cool', async (req, res, next) => {
  try {
    const { temperature } = req.body;

    if (temperature === undefined) {
      return res.status(400).json({
        success: false,
        error: 'temperature is required',
      });
    }

    const result = await req.voyager.commands.coolCamera(temperature);
    res.json({ success: true, result });
  } catch (error) {
    next(error);
  }
});

router.post('/camera/warm', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.warmCamera();
    res.json({ success: true, result });
  } catch (error) {
    next(error);
  }
});

router.post('/camera/shot', async (req, res, next) => {
  try {
    const { exposure, binning = 1, filter = 0 } = req.body;

    if (!exposure) {
      return res.status(400).json({
        success: false,
        error: 'exposure is required',
      });
    }

    const result = await req.voyager.commands.takeShot(exposure, binning, filter);
    res.json({ success: true, result });
  } catch (error) {
    next(error);
  }
});

// Utilities
router.post('/utils/autofocus', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.autofocus();
    res.json({ success: true, result });
  } catch (error) {
    next(error);
  }
});

router.post('/utils/platesolve', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.platesolve();
    res.json({ success: true, result });
  } catch (error) {
    next(error);
  }
});

// Export a function that accepts voyagerConnection to setup RoboTarget routes
export default function createRouter(voyagerConnection, io) {
  // Mount RoboTarget routes (includes test-mac routes)
  router.use('/robotarget', roboTargetRoutes(voyagerConnection, io));

  return router;
}
