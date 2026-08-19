import React, { useState, useEffect, useMemo } from 'react';
import { 
  Building2, 
  Layers, 
  Plus, 
  Trash2, 
  Power, 
  Sliders, 
  Zap, 
  Clock, 
  Activity, 
  ChevronRight, 
  ChevronDown, 
  Thermometer, 
  Wind, 
  Sparkles,
  SlidersHorizontal,
  Lightbulb
} from 'lucide-react';

// ==========================================
// 1. Types & Data Structures
// ==========================================
export type DeviceType = 'switch' | 'dimmer';

export interface DeviceItem {
  id: string;
  name: string;
  type: DeviceType;
  active: boolean;
  value: number; // 0~100 for dimmer, 0 or 1 for switch
  wattage: number; // Base wattage per device
}

export interface SpaceItem {
  id: string;
  name: string;
  telemetry: {
    temp: number; // °C
    co2: number;  // ppm
    humidity?: number; // %
  };
  devices: DeviceItem[];
}

export interface FloorItem {
  id: string;
  name: string;
  level: number;
  spaces: SpaceItem[];
}

export interface ActivityEvent {
  id: string;
  timestamp: string;
  message: string;
  level: 'info' | 'active' | 'warning';
}

// ==========================================
// 2. 한국어 초기 샘플 데이터
// ==========================================
const INITIAL_BUILDING_DATA: FloorItem[] = [
  {
    id: 'floor-3',
    name: '3층 (목양실 & 회의실)',
    level: 3,
    spaces: [
      {
        id: 'space-3-1',
        name: '목양실 (집무실)',
        telemetry: { temp: 23.5, co2: 450, humidity: 48 },
        devices: [
          { id: 'dev-3-1-1', name: '목양실 메인 전등 1호', type: 'switch', active: true, value: 1, wattage: 25 },
          { id: 'dev-3-1-2', name: '목양실 메인 전등 2호', type: 'switch', active: false, value: 0, wattage: 25 },
          { id: 'dev-3-1-3', name: '데스크 무드 조광기', type: 'dimmer', active: true, value: 75, wattage: 40 },
        ],
      },
      {
        id: 'space-3-2',
        name: '소회의실 (미팅룸)',
        telemetry: { temp: 22.0, co2: 620, humidity: 52 },
        devices: [
          { id: 'dev-3-2-1', name: '프레젠테이션 조명', type: 'switch', active: false, value: 0, wattage: 35 },
          { id: 'dev-3-2-2', name: '천장 배열 전등', type: 'switch', active: false, value: 0, wattage: 50 },
        ],
      },
    ],
  },
  {
    id: 'floor-2',
    name: '2층 (대예배실 메인홀)',
    level: 2,
    spaces: [
      {
        id: 'space-2-1',
        name: '대예배실 본당',
        telemetry: { temp: 21.8, co2: 510, humidity: 45 },
        devices: [
          { id: 'dev-2-1-1', name: '샹들리에 1구', type: 'switch', active: true, value: 1, wattage: 60 },
          { id: 'dev-2-1-2', name: '샹들리에 2구', type: 'switch', active: true, value: 1, wattage: 60 },
          { id: 'dev-2-1-3', name: '샹들리에 3구', type: 'switch', active: true, value: 1, wattage: 60 },
          { id: 'dev-2-1-4', name: '샹들리에 4구', type: 'switch', active: true, value: 1, wattage: 60 },
          { id: 'dev-2-1-5', name: '강단 액센트 조광기', type: 'dimmer', active: true, value: 85, wattage: 80 },
          { id: 'dev-2-1-6', name: '설교대 스포트라이트', type: 'switch', active: true, value: 1, wattage: 100 },
        ],
      },
      {
        id: 'space-2-2',
        name: '방송실 (AV 콘솔)',
        telemetry: { temp: 24.2, co2: 480, humidity: 40 },
        devices: [
          { id: 'dev-2-2-1', name: '콘솔 데스크 조명', type: 'switch', active: true, value: 1, wattage: 20 },
          { id: 'dev-2-2-2', name: '서버랙 배기 환풍기', type: 'switch', active: true, value: 1, wattage: 45 },
        ],
      },
    ],
  },
  {
    id: 'floor-1',
    name: '1층 (메인 로비 & 편의시설)',
    level: 1,
    spaces: [
      {
        id: 'space-1-1',
        name: '메인 웰컴 로비',
        telemetry: { temp: 22.5, co2: 430, humidity: 50 },
        devices: [
          { id: 'dev-1-1-1', name: '로비 링 조명 1호', type: 'switch', active: true, value: 1, wattage: 35 },
          { id: 'dev-1-1-2', name: '로비 링 조명 2호', type: 'switch', active: true, value: 1, wattage: 35 },
          { id: 'dev-1-1-3', name: '로비 링 조명 3호', type: 'switch', active: true, value: 1, wattage: 35 },
          { id: 'dev-1-1-4', name: '안내데스크 조명', type: 'switch', active: false, value: 0, wattage: 30 },
          { id: 'dev-1-1-5', name: '외관 파사드 조광기', type: 'dimmer', active: true, value: 60, wattage: 70 },
        ],
      },
      {
        id: 'space-1-2',
        name: '남성 화장실 (M)',
        telemetry: { temp: 21.0, co2: 410, humidity: 55 },
        devices: [
          { id: 'dev-1-2-1', name: '조명 전등', type: 'switch', active: true, value: 1, wattage: 25 },
          { id: 'dev-1-2-2', name: '고성능 환풍기', type: 'switch', active: true, value: 1, wattage: 35 },
        ],
      },
      {
        id: 'space-1-3',
        name: '여성 화장실 (F)',
        telemetry: { temp: 21.2, co2: 405, humidity: 54 },
        devices: [
          { id: 'dev-1-3-1', name: '조명 전등', type: 'switch', active: false, value: 0, wattage: 25 },
          { id: 'dev-1-3-2', name: '고성능 환풍기', type: 'switch', active: false, value: 0, wattage: 35 },
        ],
      },
      {
        id: 'space-1-4',
        name: '메인 현관 보안문',
        telemetry: { temp: 20.8, co2: 420, humidity: 48 },
        devices: [
          { id: 'dev-1-4-1', name: '스마트 전자 도어락', type: 'switch', active: true, value: 1, wattage: 15 },
          { id: 'dev-1-4-2', name: '현관 웰컴 센서등', type: 'switch', active: true, value: 1, wattage: 30 },
        ],
      },
    ],
  },
];

const LOCAL_STORAGE_KEY = 'STITCH_TABLET_BMS_STATE_KR_V1';

// ==========================================
// 3. React 컴포넌트 본체
// ==========================================
export const StitchTabletBmsDashboard: React.FC = () => {
  const [buildingData, setBuildingData] = useState<FloorItem[]>(() => {
    try {
      const saved = localStorage.getItem(LOCAL_STORAGE_KEY);
      if (saved) return JSON.parse(saved);
    } catch {
      // Fallback
    }
    return INITIAL_BUILDING_DATA;
  });

  const [selectedFloorId, setSelectedFloorId] = useState<string>('floor-2');
  const [selectedSpaceId, setSelectedSpaceId] = useState<string>('space-2-1');
  const [expandedFloors, setExpandedFloors] = useState<Record<string, boolean>>({
    'floor-3': true,
    'floor-2': true,
    'floor-1': true,
  });

  // 이벤트 피드
  const [logs, setLogs] = useState<ActivityEvent[]>([
    { id: 'log-1', timestamp: '15:42:10', message: '스마트 빌딩 관제 시스템이 정상 가동되었습니다.', level: 'info' },
    { id: 'log-2', timestamp: '15:45:00', message: '대예배실 샹들리에 조명 그룹 켜짐(ON)', level: 'active' },
  ]);

  // 실시간 시계
  const [currentTime, setCurrentTime] = useState<string>('');

  useEffect(() => {
    const updateTime = () => {
      const now = new Date();
      setCurrentTime(now.toTimeString().split(' ')[0]);
    };
    updateTime();
    const timer = setInterval(updateTime, 1000);
    return () => clearInterval(timer);
  }, []);

  // LocalStorage 저장
  useEffect(() => {
    try {
      localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(buildingData));
    } catch (e) {
      console.error('LocalStorage 저장 실패', e);
    }
  }, [buildingData]);

  // 로그 추가 헬퍼
  const addLog = (message: string, level: 'info' | 'active' | 'warning' = 'active') => {
    const newLog: ActivityEvent = {
      id: `log-${Date.now()}-${Math.random().toString(36).substr(2, 4)}`,
      timestamp: new Date().toTimeString().split(' ')[0],
      message,
      level,
    };
    setLogs((prev) => [newLog, ...prev.slice(0, 19)]);
  };

  // 파생 상태 계산
  const activeFloor = useMemo(
    () => buildingData.find((f) => f.id === selectedFloorId) || buildingData[0],
    [buildingData, selectedFloorId]
  );

  const activeSpace = useMemo(() => {
    if (!activeFloor) return null;
    return activeFloor.spaces.find((s) => s.id === selectedSpaceId) || activeFloor.spaces[0] || null;
  }, [activeFloor, selectedSpaceId]);

  // 전력 및 활성 기기 집계
  const { totalDevices, activeDevices, totalWatts } = useMemo(() => {
    let tDev = 0;
    let aDev = 0;
    let watts = 0;

    buildingData.forEach((f) => {
      f.spaces.forEach((s) => {
        s.devices.forEach((d) => {
          tDev += 1;
          if (d.active) {
            aDev += 1;
            if (d.type === 'dimmer') {
              watts += Math.round(d.wattage * (d.value / 100));
            } else {
              watts += d.wattage;
            }
          }
        });
      });
    });

    watts += 120;
    return { totalDevices: tDev, activeDevices: aDev, totalWatts: watts };
  }, [buildingData]);

  // --- 이벤트 핸들러 ---
  const handleToggleDevice = (floorId: string, spaceId: string, deviceId: string) => {
    setBuildingData((prev) =>
      prev.map((floor) => {
        if (floor.id !== floorId) return floor;
        return {
          ...floor,
          spaces: floor.spaces.map((space) => {
            if (space.id !== spaceId) return space;
            return {
              ...space,
              devices: space.devices.map((device) => {
                if (device.id !== deviceId) return device;
                const nextState = !device.active;
                addLog(
                  `[${space.name}] ${device.name} 전원이 ${nextState ? '켜졌습니다(ON)' : '꺼졌습니다(OFF)'}.`,
                  nextState ? 'active' : 'info'
                );
                return { ...device, active: nextState };
              }),
            };
          }),
        };
      })
    );
  };

  const handleSetDimmerValue = (floorId: string, spaceId: string, deviceId: string, val: number) => {
    setBuildingData((prev) =>
      prev.map((floor) => {
        if (floor.id !== floorId) return floor;
        return {
          ...floor,
          spaces: floor.spaces.map((space) => {
            if (space.id !== spaceId) return space;
            return {
              ...space,
              devices: space.devices.map((device) => {
                if (device.id !== deviceId) return device;
                return { ...device, value: val, active: val > 0 };
              }),
            };
          }),
        };
      })
    );
  };

  const handleAddFloor = () => {
    const nextLevel = buildingData.length > 0 ? Math.max(...buildingData.map((f) => f.level)) + 1 : 1;
    const newFloorId = `floor-${Date.now()}`;
    const newFloorName = `${nextLevel}층 (신규 구역)`;

    const newFloor: FloorItem = {
      id: newFloorId,
      name: newFloorName,
      level: nextLevel,
      spaces: [
        {
          id: `space-${Date.now()}-1`,
          name: '메인 스마트 공간',
          telemetry: { temp: 22.0, co2: 420, humidity: 50 },
          devices: [
            { id: `dev-${Date.now()}-1`, name: '천장 다운라이트', type: 'switch', active: true, value: 1, wattage: 30 },
          ],
        },
      ],
    };

    setBuildingData((prev) => [newFloor, ...prev]);
    setSelectedFloorId(newFloorId);
    setSelectedSpaceId(newFloor.spaces[0].id);
    setExpandedFloors((prev) => ({ ...prev, [newFloorId]: true }));
    addLog(`새로운 층이 등록되었습니다: ${newFloorName}`, 'info');
  };

  const handleDeleteFloor = (floorId: string, e: React.MouseEvent) => {
    e.stopPropagation();
    if (buildingData.length <= 1) {
      alert('최소 1개 이상의 층이 유지되어야 합니다.');
      return;
    }
    const targetFloor = buildingData.find((f) => f.id === floorId);
    setBuildingData((prev) => prev.filter((f) => f.id !== floorId));
    if (selectedFloorId === floorId) {
      const remaining = buildingData.filter((f) => f.id !== floorId);
      if (remaining.length > 0) {
        setSelectedFloorId(remaining[0].id);
        setSelectedSpaceId(remaining[0].spaces[0]?.id || '');
      }
    }
    addLog(`층이 삭제되었습니다: ${targetFloor?.name}`, 'warning');
  };

  const handleAddSpace = (floorId: string, e?: React.MouseEvent) => {
    if (e) e.stopPropagation();
    const spaceName = prompt('추가할 공간의 이름을 입력하세요:', '스마트 회의실');
    if (!spaceName) return;

    const newSpaceId = `space-${Date.now()}`;
    const newSpace: SpaceItem = {
      id: newSpaceId,
      name: spaceName,
      telemetry: { temp: 22.5, co2: 440, humidity: 48 },
      devices: [
        { id: `dev-${Date.now()}-1`, name: '기본 전등', type: 'switch', active: true, value: 1, wattage: 25 },
        { id: `dev-${Date.now()}-2`, name: '무드 조광기', type: 'dimmer', active: false, value: 0, wattage: 40 },
      ],
    };

    setBuildingData((prev) =>
      prev.map((floor) => {
        if (floor.id !== floorId) return floor;
        return {
          ...floor,
          spaces: [...floor.spaces, newSpace],
        };
      })
    );
    setSelectedFloorId(floorId);
    setSelectedSpaceId(newSpaceId);
    addLog(`새로운 공간이 등록되었습니다: [${spaceName}]`, 'info');
  };

  const handleDeleteSpace = (floorId: string, spaceId: string, e: React.MouseEvent) => {
    e.stopPropagation();
    const floor = buildingData.find((f) => f.id === floorId);
    if (!floor || floor.spaces.length <= 1) {
      alert('층 내에 최소 1개 이상의 공간이 유지되어야 합니다.');
      return;
    }
    const targetSpace = floor.spaces.find((s) => s.id === spaceId);

    setBuildingData((prev) =>
      prev.map((f) => {
        if (f.id !== floorId) return f;
        return {
          ...f,
          spaces: f.spaces.filter((s) => s.id !== spaceId),
        };
      })
    );

    if (selectedSpaceId === spaceId) {
      const remainingSpaces = floor.spaces.filter((s) => s.id !== spaceId);
      if (remainingSpaces.length > 0) {
        setSelectedSpaceId(remainingSpaces[0].id);
      }
    }
    addLog(`공간이 삭제되었습니다: ${targetSpace?.name}`, 'warning');
  };

  const handleAddDevice = () => {
    if (!activeFloor || !activeSpace) return;
    const devName = prompt('추가할 IoT 기기 이름:', 'LED 조명');
    if (!devName) return;

    const devType: DeviceType = confirm('디머(조광기)로 추가하시겠습니까?\n[확인]: 디머(0~100% 조광) / [취소]: 일반 ON/OFF 스위치') ? 'dimmer' : 'switch';

    const newDevice: DeviceItem = {
      id: `dev-${Date.now()}`,
      name: devName,
      type: devType,
      active: true,
      value: devType === 'dimmer' ? 100 : 1,
      wattage: devType === 'dimmer' ? 50 : 30,
    };

    setBuildingData((prev) =>
      prev.map((floor) => {
        if (floor.id !== activeFloor.id) return floor;
        return {
          ...floor,
          spaces: floor.spaces.map((space) => {
            if (space.id !== activeSpace.id) return space;
            return {
              ...space,
              devices: [...space.devices, newDevice],
            };
          }),
        };
      })
    );
    addLog(`새 기기가 추가되었습니다: [${devName}] (${devType === 'dimmer' ? '조광기' : '스위치'})`, 'info');
  };

  const handleDeleteDevice = (deviceId: string) => {
    if (!activeFloor || !activeSpace) return;
    const targetDev = activeSpace.devices.find((d) => d.id === deviceId);

    setBuildingData((prev) =>
      prev.map((floor) => {
        if (floor.id !== activeFloor.id) return floor;
        return {
          ...floor,
          spaces: floor.spaces.map((space) => {
            if (space.id !== activeSpace.id) return space;
            return {
              ...space,
              devices: space.devices.filter((d) => d.id !== deviceId),
            };
          }),
        };
      })
    );
    addLog(`기기가 제거되었습니다: ${targetDev?.name}`, 'warning');
  };

  const handleFloorAllOff = (floorId: string) => {
    setBuildingData((prev) =>
      prev.map((floor) => {
        if (floor.id !== floorId) return floor;
        return {
          ...floor,
          spaces: floor.spaces.map((space) => ({
            ...space,
            devices: space.devices.map((dev) => ({ ...dev, active: false, value: 0 })),
          })),
        };
      })
    );
    addLog(`[${activeFloor?.name}] 층 전체 기기 일괄 소등이 완료되었습니다.`, 'warning');
  };

  const handleBuildingMasterOff = () => {
    setBuildingData((prev) =>
      prev.map((floor) => ({
        ...floor,
        spaces: floor.spaces.map((space) => ({
          ...space,
          devices: space.devices.map((dev) => ({ ...dev, active: false, value: 0 })),
        })),
      }))
    );
    addLog('🚨 건물 전체 모든 조명 및 가전 일괄 소등 완료', 'warning');
  };

  const roomGridColsClass = useMemo(() => {
    const count = activeFloor?.spaces.length || 0;
    if (count <= 1) return 'grid-cols-1';
    if (count === 2) return 'grid-cols-2';
    if (count === 3 || count === 4) return 'grid-cols-2';
    return 'grid-cols-3';
  }, [activeFloor]);

  return (
    <div className="h-screen w-screen max-h-screen max-w-screen overflow-hidden bg-slate-950 text-slate-100 font-sans flex flex-col select-none p-3 gap-3">
      
      {/* 🌟 헤더 (8열 전체 너비) */}
      <header className="h-14 min-h-[56px] bg-slate-900/90 backdrop-blur-md border border-slate-800 rounded-xl px-4 flex items-center justify-between shadow-lg shadow-black/40 flex-shrink-0">
        <div className="flex items-center gap-3">
          <div className="w-9 h-9 rounded-lg bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 shadow-sm shadow-emerald-500/20">
            <Building2 className="w-5 h-5" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-sm font-bold tracking-tight text-white">
                비전 스마트 센터 관제 (BMS)
              </h1>
              <span className="px-2 py-0.5 rounded text-[10px] font-extrabold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">
                실시간 정상 가동
              </span>
            </div>
            <p className="text-[11px] text-slate-400 font-mono">
              태블릿 풀스크린 최적화 // 1024×768 무스크롤 관제 시스템
            </p>
          </div>
        </div>

        <div className="flex items-center gap-2 md:gap-4">
          <div className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-950/60 border border-slate-800 text-xs">
            <Zap className="w-4 h-4 text-emerald-400" />
            <span className="text-slate-400">총 전력 소비량:</span>
            <span className="font-bold text-white font-mono">{totalWatts.toLocaleString()} W</span>
          </div>

          <div className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-950/60 border border-slate-800 text-xs">
            <Activity className="w-4 h-4 text-amber-400" />
            <span className="text-slate-400">가동 중인 기기:</span>
            <span className="font-bold text-amber-300 font-mono">
              {activeDevices} / {totalDevices}개
            </span>
          </div>

          <div className="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-950/60 border border-slate-800 text-xs font-mono text-slate-300">
            <Clock className="w-4 h-4 text-slate-400" />
            <span>{currentTime || '16:00:00'}</span>
          </div>
        </div>

        <div className="flex items-center gap-2">
          <button
            onClick={handleBuildingMasterOff}
            className="h-10 px-3.5 bg-rose-600/20 hover:bg-rose-600 border border-rose-500/40 hover:border-rose-500 text-rose-300 hover:text-white rounded-lg text-xs font-extrabold transition-all flex items-center gap-2 shadow-sm active:scale-95"
            title="건물 전체 모든 조명 및 가전 즉시 소등"
          >
            <Power className="w-4 h-4" />
            <span>건물 전체 소등</span>
          </button>
        </div>
      </header>

      {/* 🌾 8열 그리드 메인 관제 */}
      <main className="flex-1 min-h-0 w-full grid grid-cols-8 gap-3 overflow-hidden">
        
        {/* 1. 좌측 건물 공간 구조도 (2열) */}
        <section className="col-span-2 h-full min-h-0 bg-slate-900/80 border border-slate-800 rounded-xl p-3 flex flex-col gap-3 overflow-hidden shadow-md">
          <div className="flex items-center justify-between pb-2 border-b border-slate-800/80">
            <div className="flex items-center gap-2 text-xs font-bold text-slate-200">
              <Layers className="w-4 h-4 text-emerald-400" />
              <span>건물 공간 구조도</span>
            </div>
            <button
              onClick={handleAddFloor}
              className="h-7 px-2 bg-emerald-500/15 hover:bg-emerald-500 border border-emerald-500/30 hover:border-emerald-400 text-emerald-400 hover:text-slate-950 rounded text-[11px] font-bold transition-all flex items-center gap-1"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>층 추가</span>
            </button>
          </div>

          <div className="flex-1 min-h-0 overflow-y-auto space-y-2 pr-1 custom-scrollbar">
            {buildingData.map((floor) => {
              const isFloorSelected = floor.id === selectedFloorId;
              const isExpanded = expandedFloors[floor.id] ?? true;
              const floorActiveDevs = floor.spaces.reduce(
                (acc, s) => acc + s.devices.filter((d) => d.active).length,
                0
              );

              return (
                <div
                  key={floor.id}
                  className={`rounded-lg border transition-all ${
                    isFloorSelected
                      ? 'bg-slate-800/80 border-emerald-500/40 shadow-sm'
                      : 'bg-slate-950/40 border-slate-800/60 hover:border-slate-700'
                  }`}
                >
                  <div
                    onClick={() => {
                      setSelectedFloorId(floor.id);
                      if (floor.spaces.length > 0) {
                        setSelectedSpaceId(floor.spaces[0].id);
                      }
                    }}
                    className="min-h-[48px] px-3 py-2 flex items-center justify-between cursor-pointer group"
                  >
                    <div className="flex items-center gap-2 min-w-0">
                      <button
                        onClick={(e) => {
                          e.stopPropagation();
                          setExpandedFloors((prev) => ({ ...prev, [floor.id]: !isExpanded }));
                        }}
                        className="text-slate-400 hover:text-white p-0.5"
                      >
                        {isExpanded ? <ChevronDown className="w-4 h-4" /> : <ChevronRight className="w-4 h-4" />}
                      </button>
                      <span className="text-xs font-bold text-slate-100 truncate">{floor.name}</span>
                    </div>

                    <div className="flex items-center gap-1.5">
                      <span className="text-[10px] font-mono px-1.5 py-0.5 rounded bg-slate-900 border border-slate-700 text-emerald-400 font-bold">
                        {floorActiveDevs}개 ON
                      </span>
                      <button
                        onClick={(e) => handleAddSpace(floor.id, e)}
                        className="p-1 text-slate-400 hover:text-emerald-400 hover:bg-slate-800 rounded transition-colors"
                        title="공간 추가"
                      >
                        <Plus className="w-3.5 h-3.5" />
                      </button>
                      <button
                        onClick={(e) => handleDeleteFloor(floor.id, e)}
                        className="p-1 text-slate-500 hover:text-rose-400 hover:bg-slate-800 rounded transition-colors"
                        title="층 삭제"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                      </button>
                    </div>
                  </div>

                  {isExpanded && (
                    <div className="pl-6 pr-2 pb-2 space-y-1">
                      {floor.spaces.map((space) => {
                        const isSpaceSelected = space.id === selectedSpaceId && isFloorSelected;
                        const spaceActiveCount = space.devices.filter((d) => d.active).length;

                        return (
                          <div
                            key={space.id}
                            onClick={() => {
                              setSelectedFloorId(floor.id);
                              setSelectedSpaceId(space.id);
                            }}
                            className={`min-h-[40px] px-2.5 py-1.5 rounded flex items-center justify-between text-xs cursor-pointer transition-all ${
                              isSpaceSelected
                                ? 'bg-emerald-500/20 text-emerald-300 border-l-4 border-emerald-500 font-bold'
                                : 'text-slate-400 hover:bg-slate-900 hover:text-slate-200'
                            }`}
                          >
                            <span className="truncate">{space.name}</span>
                            <div className="flex items-center gap-1">
                              <span className={`w-2 h-2 rounded-full ${spaceActiveCount > 0 ? 'bg-emerald-400 shadow-sm shadow-emerald-400' : 'bg-slate-700'}`} />
                              <button
                                onClick={(e) => handleDeleteSpace(floor.id, space.id, e)}
                                className="p-1 text-slate-600 hover:text-rose-400 opacity-0 group-hover:opacity-100 transition-opacity"
                              >
                                <Trash2 className="w-3 h-3" />
                              </button>
                            </div>
                          </div>
                        );
                      })}
                    </div>
                  )}
                </div>
              );
            })}
          </div>

          <div className="h-28 min-h-[112px] bg-slate-950/80 border border-slate-800 rounded-lg p-2 flex flex-col justify-between flex-shrink-0">
            <div className="flex items-center justify-between pb-1 border-b border-slate-800/60 text-[10px] font-bold text-slate-400">
              <span className="flex items-center gap-1 font-mono">
                <Sparkles className="w-3 h-3 text-emerald-400" />
                실시간 이벤트 피드
              </span>
              <span className="text-emerald-400 font-mono">LIVE</span>
            </div>
            <div className="flex-1 min-h-0 overflow-y-auto space-y-1 font-mono text-[10px] pr-1 custom-scrollbar">
              {logs.slice(0, 5).map((log) => (
                <div key={log.id} className="text-slate-300 leading-tight truncate">
                  <span className="text-slate-500">[{log.timestamp}]</span>{' '}
                  <span className={log.level === 'warning' ? 'text-rose-400' : log.level === 'active' ? 'text-emerald-400' : 'text-slate-300'}>
                    {log.message}
                  </span>
                </div>
              ))}
            </div>
          </div>
        </section>

        {/* 2. 중앙 층별 공간 관제 (4열) */}
        <section className="col-span-4 h-full min-h-0 bg-slate-900/80 border border-slate-800 rounded-xl p-3 flex flex-col gap-3 overflow-hidden shadow-md">
          <div className="flex items-center justify-between pb-2 border-b border-slate-800/80">
            <div className="flex items-center gap-2">
              <div className="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse" />
              <h2 className="text-xs font-extrabold text-white tracking-wide">
                {activeFloor?.name || '층별 공간 관제'}
              </h2>
            </div>
            
            <div className="flex items-center gap-2">
              <button
                onClick={() => activeFloor && handleFloorAllOff(activeFloor.id)}
                className="h-7 px-2.5 bg-rose-500/10 hover:bg-rose-600 border border-rose-500/30 hover:border-rose-500 text-rose-300 hover:text-white rounded text-[11px] font-bold transition-all"
              >
                층 전체 소등
              </button>
              <button
                onClick={() => activeFloor && handleAddSpace(activeFloor.id)}
                className="h-7 px-2.5 bg-emerald-500/15 hover:bg-emerald-500 border border-emerald-500/30 text-emerald-400 hover:text-slate-950 rounded text-[11px] font-bold transition-all flex items-center gap-1"
              >
                <Plus className="w-3.5 h-3.5" />
                <span>공간 추가</span>
              </button>
            </div>
          </div>

          <div className={`flex-1 min-h-0 grid ${roomGridColsClass} gap-3 overflow-y-auto pr-1 custom-scrollbar`}>
            {activeFloor?.spaces.map((space) => {
              const isSelected = space.id === selectedSpaceId;
              const activeDevs = space.devices.filter((d) => d.active);
              const isLit = activeDevs.length > 0;

              return (
                <div
                  key={space.id}
                  onClick={() => setSelectedSpaceId(space.id)}
                  className={`relative rounded-xl border p-3 flex flex-col justify-between transition-all cursor-pointer ${
                    isSelected
                      ? 'border-emerald-500 ring-2 ring-emerald-500/20'
                      : 'border-slate-800/80 hover:border-slate-700'
                  } ${
                    isLit
                      ? 'bg-gradient-to-b from-slate-900/90 to-emerald-950/20 shadow-lg shadow-emerald-950/30'
                      : 'bg-slate-950/60'
                  }`}
                >
                  <div className="flex items-start justify-between">
                    <div>
                      <h3 className="text-sm font-bold text-white leading-tight">{space.name}</h3>
                      <span className="text-[10px] font-mono text-slate-400">
                        {activeDevs.length}/{space.devices.length}개 기기 가동 중
                      </span>
                    </div>

                    <span
                      className={`px-2 py-0.5 rounded text-[10px] font-bold font-mono ${
                        isLit
                          ? 'bg-emerald-500 text-slate-950 shadow-sm shadow-emerald-500/30'
                          : 'bg-slate-800 text-slate-400'
                      }`}
                    >
                      {isLit ? '가동 중' : '대기 중'}
                    </span>
                  </div>

                  <div className="grid grid-cols-2 gap-2 my-3">
                    <div className="bg-slate-900/90 border border-slate-800 rounded-lg p-2 flex items-center justify-between">
                      <div className="flex items-center gap-1.5 text-slate-400 text-xs">
                        <Thermometer className="w-4 h-4 text-emerald-400" />
                        <span>실내 온도</span>
                      </div>
                      <span className="font-bold text-white font-mono text-sm">{space.telemetry.temp}°C</span>
                    </div>

                    <div className="bg-slate-900/90 border border-slate-800 rounded-lg p-2 flex items-center justify-between">
                      <div className="flex items-center gap-1.5 text-slate-400 text-xs">
                        <Wind className="w-4 h-4 text-cyan-400" />
                        <span>이산화탄소</span>
                      </div>
                      <span className="font-bold text-white font-mono text-sm">{space.telemetry.co2} ppm</span>
                    </div>
                  </div>

                  <div className="bg-slate-950/80 rounded-lg p-2 border border-slate-800/60">
                    <div className="text-[10px] font-bold text-slate-400 mb-1.5 flex justify-between">
                      <span>연결된 부하 채널</span>
                      <span className="font-mono text-emerald-400">
                        {space.devices.reduce((sum, d) => sum + (d.active ? d.wattage : 0), 0)} W
                      </span>
                    </div>
                    <div className="flex flex-wrap gap-1.5">
                      {space.devices.map((device) => (
                        <div
                          key={device.id}
                          onClick={(e) => {
                            e.stopPropagation();
                            if (activeFloor) handleToggleDevice(activeFloor.id, space.id, device.id);
                          }}
                          className={`h-7 px-2 rounded flex items-center gap-1.5 text-[11px] font-bold font-mono transition-all ${
                            device.active
                              ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/50 shadow-sm shadow-emerald-500/20'
                              : 'bg-slate-900 text-slate-500 border border-slate-800 hover:text-slate-300'
                          }`}
                        >
                          <span className={`w-1.5 h-1.5 rounded-full ${device.active ? 'bg-emerald-400 animate-pulse' : 'bg-slate-600'}`} />
                          <span className="truncate max-w-[90px]">{device.name}</span>
                        </div>
                      ))}
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        </section>

        {/* 3. 우측 공간별 기기 제어기 (2열) */}
        <section className="col-span-2 h-full min-h-0 bg-slate-900/80 border border-slate-800 rounded-xl p-3 flex flex-col gap-3 overflow-hidden shadow-md">
          <div className="flex items-center justify-between pb-2 border-b border-slate-800/80">
            <div className="flex items-center gap-2 text-xs font-bold text-slate-200">
              <Sliders className="w-4 h-4 text-emerald-400" />
              <span>공간별 기기 제어기</span>
            </div>
            <button
              onClick={handleAddDevice}
              className="h-7 px-2 bg-emerald-500/15 hover:bg-emerald-500 border border-emerald-500/30 text-emerald-400 hover:text-slate-950 rounded text-[11px] font-bold transition-all flex items-center gap-1"
            >
              <Plus className="w-3.5 h-3.5" />
              <span>기기 추가</span>
            </button>
          </div>

          <div className="bg-slate-950/80 border border-slate-800 rounded-lg p-2.5 flex items-center justify-between">
            <div>
              <span className="text-[10px] font-mono text-slate-400">선택된 공간</span>
              <h3 className="text-sm font-extrabold text-white">{activeSpace?.name || '선택된 공간 없음'}</h3>
            </div>
            <span className="px-2 py-1 rounded bg-slate-900 border border-slate-700 text-emerald-400 font-mono text-xs font-bold">
              {activeSpace?.devices.filter((d) => d.active).length} / {activeSpace?.devices.length}개 가동 중
            </span>
          </div>

          <div className="flex-1 min-h-0 overflow-y-auto space-y-2.5 pr-1 custom-scrollbar">
            {activeSpace?.devices.map((device) => {
              const isSwitch = device.type === 'switch';

              return (
                <div
                  key={device.id}
                  className={`rounded-xl border p-3 transition-all ${
                    device.active
                      ? 'bg-gradient-to-b from-slate-900 to-emerald-950/20 border-emerald-500/40 shadow-sm shadow-emerald-500/10'
                      : 'bg-slate-950/50 border-slate-800'
                  }`}
                >
                  <div className="flex items-center justify-between mb-2">
                    <div className="flex items-center gap-2">
                      <div
                        className={`w-7 h-7 rounded-lg flex items-center justify-center ${
                          device.active ? 'bg-emerald-500/20 text-emerald-400' : 'bg-slate-800 text-slate-500'
                        }`}
                      >
                        {isSwitch ? <Lightbulb className="w-4 h-4" /> : <SlidersHorizontal className="w-4 h-4" />}
                      </div>
                      <div>
                        <h4 className="text-xs font-bold text-white leading-tight">{device.name}</h4>
                        <span className="text-[10px] font-mono text-slate-400">
                          {isSwitch ? '스위치' : '조광기(디머)'} • {device.wattage}W
                        </span>
                      </div>
                    </div>

                    <button
                      onClick={() => handleDeleteDevice(device.id)}
                      className="text-slate-600 hover:text-rose-400 p-1 transition-colors"
                      title="기기 삭제"
                    >
                      <Trash2 className="w-3.5 h-3.5" />
                    </button>
                  </div>

                  {isSwitch ? (
                    <button
                      onClick={() => activeFloor && activeSpace && handleToggleDevice(activeFloor.id, activeSpace.id, device.id)}
                      className={`w-full h-12 rounded-lg font-bold text-xs flex items-center justify-between px-3.5 transition-all shadow-inner active:scale-98 ${
                        device.active
                          ? 'bg-emerald-500 text-slate-950 shadow-emerald-500/30 font-extrabold'
                          : 'bg-slate-800 text-slate-400 border border-slate-700 hover:border-slate-600'
                      }`}
                    >
                      <span>{device.active ? '전원 켜짐 (ON)' : '전원 꺼짐 (OFF)'}</span>
                      <Power className={`w-4 h-4 ${device.active ? 'text-slate-950' : 'text-slate-400'}`} />
                    </button>
                  ) : (
                    <div className="space-y-1.5 pt-1">
                      <div className="flex justify-between text-[11px] font-mono">
                        <span className="text-slate-400">밝기 조절 레벨</span>
                        <span className="font-bold text-emerald-400">{device.value}%</span>
                      </div>
                      <input
                        type="range"
                        min={0}
                        max={100}
                        value={device.value}
                        onChange={(e) =>
                          activeFloor &&
                          activeSpace &&
                          handleSetDimmerValue(activeFloor.id, activeSpace.id, device.id, Number(e.target.value))
                        }
                        className="w-full h-2 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-emerald-500"
                      />
                    </div>
                  )}
                </div>
              );
            })}

            {(!activeSpace || activeSpace.devices.length === 0) && (
              <div className="h-32 flex flex-col items-center justify-center text-slate-500 text-xs gap-2">
                <span>등록된 기기가 없습니다</span>
                <button
                  onClick={handleAddDevice}
                  className="px-3 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded text-xs"
                >
                  기기 추가하기
                </button>
              </div>
            )}
          </div>
        </section>

      </main>
    </div>
  );
};

export default StitchTabletBmsDashboard;
