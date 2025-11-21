<?php
// evaluasi_Nilai.php
?>

<link rel="stylesheet" href="styles/evaluasi_Nilai.css">

<div class="container-evaluasi">
  <h2>Form Penilaian Mahasiswa Bimbingan</h2>
  <table id="tabelNilai">
    <thead>
      <tr>
        <th>No</th>
        <th>Nama Mahasiswa</th>
        <th>NIM</th>
        <th>Kedisiplinan</th>
        <th>Tanggung Jawab</th>
        <th>Kerjasama</th>
        <th>Laporan</th>
        <th>Total</th>
        <th>Catatan Dosen</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>

  <p class="note">
    <i class="fas fa-info-circle"></i> Penilaian diisi oleh dosen pembimbing berdasarkan hasil evaluasi dan laporan kegiatan mahasiswa.
  </p>
</div>

<script>
  const dataMahasiswa = [
    { nama: "Ahmad Fauzan", nim: "E31241111" },
    { nama: "Siti Rahmawati", nim: "E31241122" },
    { nama: "Bagus Setiawan", nim: "E31241133" },
  ];

  const tbody = document.querySelector("#tabelNilai tbody");

  dataMahasiswa.forEach((mhs, i) => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${i + 1}</td>
      <td>${mhs.nama}</td>
      <td>${mhs.nim}</td>
      <td><input type="number" min="0" max="100" class="nilai" /></td>
      <td><input type="number" min="0" max="100" class="nilai" /></td>
      <td><input type="number" min="0" max="100" class="nilai" /></td>
      <td><input type="number" min="0" max="100" class="nilai" /></td>
      <td class="nilai-total">0</td>
      <td><textarea placeholder="Masukkan catatan singkat..."></textarea></td>
      <td><button class="btn-simpan">Simpan</button></td>
    `;
    tbody.appendChild(row);

    const inputs = row.querySelectorAll(".nilai");
    const totalCell = row.querySelector(".nilai-total");
    const btnSimpan = row.querySelector(".btn-simpan");

    inputs.forEach((input) => {
      input.addEventListener("input", () => {
        let total = 0;
        inputs.forEach((n) => {
          total += parseInt(n.value || 0);
        });
        totalCell.textContent = (total / inputs.length).toFixed(1);
      });
    });

    btnSimpan.addEventListener("click", () => {
      alert(`Nilai untuk ${mhs.nama} berhasil disimpan!`);
    });
  });
</script>
