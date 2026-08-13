/**
 * 농장 프로필 & 장비 커스터마이징 설정 페이지
 */

import { useState } from 'react'
import { styled } from '../lib/stitches.config'
import { useSmartFarmStore } from '../stores/smartfarm.store'

export function SettingsPage() {
  const { farmName, farmId } = useSmartFarmStore()
  const [name, setName] = useState('설향 딸기 스마트팜')
  const [crop, setCrop] = useState('strawberry')
  const [shadeScreen, setShadeScreen] = useState(true)
  const [waterPump, setWaterPump] = useState(true)
  const [cctvUrl, setCctvUrl] = useState('rtsp://admin:password@192.168.1.101:554/stream1')
  const [saved, setSaved] = useState(false)

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    useSmartFarmStore.setState((state) => {
      state.farmName = name
    })
    setSaved(true)
    setTimeout(() => setSaved(false), 3000)
  }

  return (
    <PageWrapper>
      <PageHeader>
        <h1>⚙️ 농장 프로필 & 장비 커스터마이징</h1>
        <p>농장 이름, 주요 재배 작물, 차양막/양수기 제어 및 TP-Link Tapo CCTV 카메라 설정</p>
      </PageHeader>

      <FormCard onSubmit={handleSubmit}>
        <FormGroup>
          <FormLabel>🏡 스마트팜 이름</FormLabel>
          <FormInput value={name} onChange={(e) => setName(e.target.value)} />
        </FormGroup>

        <FormGroup>
          <FormLabel>🍓 주요 재배 작물 선택</FormLabel>
          <FormSelect value={crop} onChange={(e) => setCrop(e.target.value)}>
            <option value="strawberry">🍓 설향 딸기 (수경 재배)</option>
            <option value="cherry_tomato">🍅 방울토마토</option>
            <option value="cucumber">🥒 오이</option>
            <option value="paprika">🫑 파프리카</option>
          </FormSelect>
        </FormGroup>

        <FormGroup>
          <FormLabel>🪟 추가 장비 운영 옵션</FormLabel>
          <CheckboxGroup>
            <CheckboxLabel>
              <input
                type="checkbox"
                checked={shadeScreen}
                onChange={(e) => setShadeScreen(e.target.checked)}
              />
              <span>차광막 개폐기 연동 운영</span>
            </CheckboxLabel>

            <CheckboxLabel>
              <input
                type="checkbox"
                checked={waterPump}
                onChange={(e) => setWaterPump(e.target.checked)}
              />
              <span>자동 양수기/관수 펌프 제어</span>
            </CheckboxLabel>
          </CheckboxGroup>
        </FormGroup>

        <FormGroup>
          <FormLabel>📹 TP-Link Tapo CCTV RTSP 스트림 주소</FormLabel>
          <FormInput value={cctvUrl} onChange={(e) => setCctvUrl(e.target.value)} />
        </FormGroup>

        <SaveButton type="submit">💾 설정 저장 및 대시보드 반영</SaveButton>

        {saved && <SuccessMessage>🎉 설정이 성공적으로 저장되었습니다!</SuccessMessage>}
      </FormCard>
    </PageWrapper>
  )
}

const PageWrapper = styled('div', { padding: '$6', display: 'flex', flexDirection: 'column', gap: '$5' })
const PageHeader = styled('div', {
  '& h1': { fontSize: '$2xl', fontWeight: '$bold', color: '$textPrimary' },
  '& p': { fontSize: '$md', color: '$textSecondary', marginTop: '$1' },
})
const FormCard = styled('form', {
  background: '$bgCard',
  border: '1px solid $border',
  borderRadius: '$xl',
  padding: '$6',
  display: 'flex',
  flexDirection: 'column',
  gap: '$5',
  maxWidth: '700px',
})
const FormGroup = styled('div', { display: 'flex', flexDirection: 'column', gap: '$2' })
const FormLabel = styled('label', { fontSize: '$md', fontWeight: '$semibold', color: '$textPrimary' })
const FormInput = styled('input', {
  padding: '$3 $4',
  borderRadius: '$md',
  border: '1px solid $border',
  fontSize: '$md',
  outline: 'none',
  '&:focus': { borderColor: '$primary' },
})
const FormSelect = styled('select', {
  padding: '$3 $4',
  borderRadius: '$md',
  border: '1px solid $border',
  fontSize: '$md',
  outline: 'none',
  '&:focus': { borderColor: '$primary' },
})
const CheckboxGroup = styled('div', { display: 'flex', gap: '$5', marginTop: '$1' })
const CheckboxLabel = styled('label', {
  display: 'flex',
  alignItems: 'center',
  gap: '$2',
  cursor: 'pointer',
  fontSize: '$md',
  fontWeight: '$medium',
  '& input': { size: '18px' },
})
const SaveButton = styled('button', {
  background: '$primary',
  color: '$textInverse',
  border: 'none',
  padding: '$3 $6',
  borderRadius: '$md',
  fontSize: '$md',
  fontWeight: '$bold',
  cursor: 'pointer',
  alignSelf: 'flex-start',
  '&:hover': { background: '$primaryHover' },
})
const SuccessMessage = styled('div', {
  color: '$success',
  fontWeight: '$bold',
  fontSize: '$md',
})
