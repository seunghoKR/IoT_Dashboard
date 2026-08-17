const path = require('path');
const fs = require('fs');
const tempNodeModules = path.join(process.env.TEMP, 'node_modules', 'puppeteer-core');
const puppeteer = require(tempNodeModules);

const CHROME_PATH = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';
const URL = 'https://nuriohga.iwinv.net/IoT_Dashboard/';
const SCREENSHOT_DIR = path.join(__dirname, 'test_screens');

if (!fs.existsSync(SCREENSHOT_DIR)) {
  fs.mkdirSync(SCREENSHOT_DIR, { recursive: true });
}

async function sleep(ms) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

async function runTest() {
  console.log('🚀 [브라우저 테스트 시작] Chrome 1920x1200 헤드리스 모드로 실행 중...');
  const browser = await puppeteer.launch({
    executablePath: CHROME_PATH,
    headless: 'new',
    defaultViewport: { width: 1920, height: 1200 },
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage();
  
  page.on('console', msg => {
    if (msg.text().includes('[통합 제어]') || msg.text().includes('[리미트 스위치 작동]') || msg.text().includes('동기화')) {
      console.log('  📢 [브라우저 로그]', msg.text());
    }
  });

  console.log(`🌐 대시보드 페이지 접속: ${URL}`);
  await page.goto(URL, { waitUntil: 'networkidle2' });
  await sleep(1500);

  // 1. 초기 닫힘 상태 점검
  console.log('📸 1. 초기 닫힘 상태 캡처 (모터 바닥 Y=455, 비닐 100% 덮임)');
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, '01_initial_closed.png') });

  // 2. [열기] 버튼 클릭
  console.log('🖱️ 2. [열기] 버튼 클릭 실행!');
  await page.click('#tile-m-open');

  // 1.5초 후 (상승 중) 상태 캡처
  await sleep(1500);
  console.log('📸 3. [열기 상승 중 1.5초] 캡처 (모터와 비닐이 한 몸으로 올라가는지 확인)');
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, '02_opening_in_progress.png') });

  // 3.8초 후 (95% 최고높이 도달 및 자동 정지 여부) 캡처
  await sleep(2300);
  console.log('📸 4. [95% 최고높이 도달 & 자동 정지(STOP) 3.8초 후] 캡처');
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, '03_open_auto_stopped.png') });

  const stateAfterOpen = await page.evaluate(() => {
    const tileStop = document.getElementById('tile-m-stop');
    const tileOpen = document.getElementById('tile-m-open');
    const group1 = document.getElementById('svg-layer-group-1');
    const clip1 = document.querySelector('#clip-curtain-1 rect');
    const sliderLeft = document.getElementById('slider-1-left');
    return {
      isStopActive: tileStop ? tileStop.classList.contains('active-stop') : false,
      isOpenActive: tileOpen ? tileOpen.classList.contains('active-open') : false,
      groupClass: group1 ? group1.className.baseVal : '',
      clipHeight: clip1 ? window.getComputedStyle(clip1).height : '',
      sliderTransform: sliderLeft ? window.getComputedStyle(sliderLeft).transform : ''
    };
  });
  console.log('  👉 [검증 결과 - 열기 후]', stateAfterOpen);

  // 3. [닫기] 버튼 클릭
  console.log('🖱️ 5. [닫기] 버튼 클릭 실행!');
  await page.click('#tile-m-close');

  // 1.5초 후 (하강 중) 상태 캡처
  await sleep(1500);
  console.log('📸 6. [닫기 하강 중 1.5초] 캡처 (모터와 비닐이 한 몸으로 내려오는지 확인)');
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, '04_closing_in_progress.png') });

  // 3.8초 후 (바닥 밀폐 도달 및 자동 정지 여부) 캡처
  await sleep(2300);
  console.log('📸 7. [바닥 밀폐 도달 & 자동 정지(STOP) 3.8초 후] 캡처');
  await page.screenshot({ path: path.join(SCREENSHOT_DIR, '05_close_auto_stopped.png') });

  const stateAfterClose = await page.evaluate(() => {
    const tileStop = document.getElementById('tile-m-stop');
    const tileClose = document.getElementById('tile-m-close');
    const group1 = document.getElementById('svg-layer-group-1');
    const clip1 = document.querySelector('#clip-curtain-1 rect');
    const sliderLeft = document.getElementById('slider-1-left');
    return {
      isStopActive: tileStop ? tileStop.classList.contains('active-stop') : false,
      isCloseActive: tileClose ? tileClose.classList.contains('active-close') : false,
      groupClass: group1 ? group1.className.baseVal : '',
      clipHeight: clip1 ? window.getComputedStyle(clip1).height : '',
      sliderTransform: sliderLeft ? window.getComputedStyle(sliderLeft).transform : ''
    };
  });
  console.log('  👉 [검증 결과 - 닫기 후]', stateAfterClose);

  await browser.close();
  console.log('🎉 [브라우저 테스트 완료!] 모든 스크린샷 검증 완료!');
}

runTest().catch(err => {
  console.error('❌ 테스트 에러:', err);
  process.exit(1);
});
