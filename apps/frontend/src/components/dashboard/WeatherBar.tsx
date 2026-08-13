/**
 * 외부 기상 상태 바 컴포넌트
 */

import { styled } from '../../lib/stitches.config'
import type { SmartFarmStore } from '../../stores/smartfarm.store'

interface WeatherBarProps {
  weather: SmartFarmStore['outdoorWeather']
}

export function WeatherBar({ weather }: WeatherBarProps) {
  if (!weather) {
    return (
      <WeatherCard>
        <WeatherItem>🌤 외부 기상 데이터 수신 중...</WeatherItem>
      </WeatherCard>
    )
  }

  return (
    <WeatherCard>
      <WeatherTitle>외부 환경</WeatherTitle>
      <WeatherItems>
        <WeatherItem>🌡 {weather.temperature.toFixed(1)}°C</WeatherItem>
        <WeatherItem>💧 {weather.humidity.toFixed(0)}%</WeatherItem>
        <WeatherItem warn={weather.windSpeed > 6}>
          💨 {weather.windSpeed.toFixed(1)}m/s
          {weather.windSpeed > 8 && ' ⚠️ 강풍'}
        </WeatherItem>
        <WeatherItem warn={weather.rainDetected}>
          {weather.rainDetected ? '🌧 강우 감지!' : '☀️ 맑음'}
        </WeatherItem>
        <WeatherItem>☀️ {weather.solarRadiation.toFixed(0)}W/m²</WeatherItem>
      </WeatherItems>
    </WeatherCard>
  )
}

const WeatherCard = styled('div', {
  flex: 1,
  background: '$bgCard',
  border: '1px solid $border',
  borderRadius: '$md',
  padding: '$4 $5',
  display: 'flex',
  flexDirection: 'column',
  gap: '$2',
  boxShadow: '$sm',
})

const WeatherTitle = styled('span', {
  fontSize: '$xs',
  fontWeight: '$semibold',
  color: '$textMuted',
  textTransform: 'uppercase',
  letterSpacing: '$widest',
})

const WeatherItems = styled('div', {
  display: 'flex',
  gap: '$4',
  flexWrap: 'wrap',
})

const WeatherItem = styled('span', {
  fontSize: '$md',
  fontWeight: '$medium',
  color: '$textPrimary',

  variants: {
    warn: {
      true: { color: '$warning', fontWeight: '$bold' },
      false: {},
    },
  },
})
