import puppeteer from 'puppeteer-core';
import fs from 'fs';
import path from 'path';

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const URL = 'https://nuriohga.iwinv.net/IoT_Dashboard/';
const SCREENSHOT_DIR = path.join(process.cwd(), 'test_screens');

if (!fs.existsSync(SCREENSHOT_DIR)) {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

async function runTest() {
  console.log('🚀 [브라우저 테스트 시작] Chrome 브라우저 실행 중...');
  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: 'new',
    defaultViewport: { width: 1920, height: 1200 },
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage();
  
  // 콘솔 로그 수집
  page.on('console', msg => console.log('  [브라우저 콘솔]', msg.type(), msg.text()));

  console.log(`🌐 대시보드 페이지 접속: ${URL}`);
  await page.goto(URL, { waitUntil: 'networkidle2' });
  await page.waitForTimeout(2000);

  // 1. 초기 닫힘 상태 점검
  console.log('📸 1. 초기 닫힘 상태 스크린샷');
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, '01_initial_closed.png') });

  // 2. [열기] 버튼 클릭
  console.log('🖱️ 2. [열기] 버튼 클릭');
  await page.click('#btn-action-open');

  // 1.5초 후 (상승 중) 상태 캡처
  await page.waitForTimeout(1500);
  console.log('📸 3. [열기 주행 중 1.5초] 스크린샷');
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, '02_opening_progress.png') });

  // 3.8초 후 (95% 최고높이 도달 및 자동 정지 여부) 캡처
  await page.waitForTimeout(2300);
  console.log('📸 4. [95% 최고높이 도달 & 자동 정지 완료 3.8초] 스크린샷');
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, '03_open_auto_stopped.png') });

  const isStopActiveAfterOpen = await page.evaluate(() => {
    const btnStop = document.getElementById('btn-action-stop');
    const btnOpen = document.getElementById('btn-action-open');
    return {
      stopActive: btnStop ? btnStop.classList.contains('active-stop') : false,
      openActive: btnOpen ? btnOpen.classList.contains('active-open') : false
    };
  });
  console.log('  👉 열기 후 자동 정지 상태 검증:', isStopActiveAfterOpen);

  // 3. [닫기] 버튼 클릭
  console.log('🖱️ 5. [닫기] 버튼 클릭');
  await page.click('#btn-action-close');

  // 1.5초 후 (하강 중) 상태 캡처
  await page.waitForTimeout(1500);
  console.log('📸 6. [닫기 주행 중 1.5초] 스크린샷');
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, '04_closing_progress.png') });

  // 3.8초 후 (바닥 밀폐 도달 및 자동 정지 여부) 캡처
  await page.waitForTimeout(2300);
  console.log('📸 7. [바닥 밀폐 도달 & 자동 정지 완료 3.8초] 스크린샷');
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, '05_close_auto_stopped.png') });

  const isStopActiveAfterClose = await page.evaluate(() => {
    const btnStop = document.getElementById('btn-action-stop');
    const btnClose = document.getElementById('btn-action-close');
    return {
      stopActive: btnStop ? btnStop.classList.contains('active-stop') : false,
      closeActive: btnClose ? btnClose.classList.contains('active-close') : false
    };
  });
  console.log('  👉 닫기 후 자동 정지 상태 검증:', isStopActiveAfterClose);

  await browser.close();
  console.log('✅ [브라우저 테스트 완료] 모든 스크린샷이 test_screens/ 폴더에 저장되었습니다.');
}

runTest().catch(err => {
  console.error('❌ 테스트 에러 발생:', err);
  process.exit(1);
});
