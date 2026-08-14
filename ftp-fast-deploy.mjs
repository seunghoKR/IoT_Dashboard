import net from 'net';
import fs from 'fs';

const filesToUpload = [
  { local: 'web_deploy/api.php', remote: '/public_html/IoT_Dashboard/api.php' },
  { local: 'web_deploy/index.php', remote: '/public_html/IoT_Dashboard/index.php' }
];

async function deploy() {
  for (const item of filesToUpload) {
    await uploadSingleFile(item.local, item.remote);
  }
  console.log('ALL FILES DEPLOYED FAST!');
}

function uploadSingleFile(localPath, remotePath) {
  return new Promise((resolve, reject) => {
    const socket = net.createConnection(21, '115.68.168.215');
    let state = 'INIT';

    socket.on('data', (data) => {
      const msg = data.toString();

      if (msg.startsWith('220') && state === 'INIT') {
        state = 'USER';
        socket.write('USER nuriohga\r\n');
      } else if (msg.startsWith('331') && state === 'USER') {
        state = 'PASS';
        socket.write('PASS seungho0409#\r\n');
      } else if (msg.startsWith('230') && state === 'PASS') {
        state = 'TYPE';
        socket.write('TYPE I\r\n');
      } else if (msg.startsWith('200') && state === 'TYPE') {
        state = 'PASV';
        socket.write('PASV\r\n');
      } else if (msg.startsWith('227') && state === 'PASV') {
        const match = msg.match(/\((\d+),(\d+),(\d+),(\d+),(\d+),(\d+)\)/);
        if (match) {
          const host = `${match[1]}.${match[2]}.${match[3]}.${match[4]}`;
          const port = parseInt(match[5]) * 256 + parseInt(match[6]);

          const dataSocket = net.createConnection(port, host, () => {
            const fileData = fs.readFileSync(localPath);
            dataSocket.write(fileData, () => {
              dataSocket.end();
            });
          });

          dataSocket.on('close', () => {
            // data socket closed
          });

          state = 'STOR';
          socket.write(`STOR ${remotePath}\r\n`);
        }
      } else if (msg.startsWith('150') && state === 'STOR') {
        // transfer starting
      } else if (msg.startsWith('226')) {
        console.log(`✅ Uploaded ${localPath} -> ${remotePath}`);
        socket.write('QUIT\r\n');
        socket.end();
        resolve();
      }
    });

    socket.on('error', reject);
  });
}

deploy().catch(console.error);
