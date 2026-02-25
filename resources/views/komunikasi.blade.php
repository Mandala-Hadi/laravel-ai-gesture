<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Komunikasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-100 via-white to-blue-200 min-h-screen p-6">

<div class="max-w-4xl mx-auto">

    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <button onclick="goBack()" 
            class="bg-gray-800 text-white px-4 py-2 rounded-xl hover:bg-gray-700 transition shadow-md">
            ← Kembali
        </button>

        <h2 class="text-3xl font-bold text-gray-800 text-center flex-1">
            Mode Komunikasi Manual
        </h2>
    </div>

    <!-- Display Text -->
    <div class="bg-white p-8 rounded-3xl shadow-xl mb-8 text-center border border-gray-200">
        <p id="outputText" class="text-2xl font-semibold text-gray-700">
            Pilih atau ketik pesan...
        </p>
    </div>

    <!-- Quick Buttons -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-5 mb-8">
        <button onclick="speak('Halo')" class="btn-primary">Halo</button>
        <button onclick="speak('Tolong')" class="btn-primary">Tolong</button>
        <button onclick="speak('Saya lapar')" class="btn-primary">Saya Lapar</button>
        <button onclick="speak('Saya haus')" class="btn-primary">Saya Haus</button>
        <button onclick="speak('Terima kasih')" class="btn-primary">Terima Kasih</button>
        <button onclick="speak('Saya butuh bantuan')" class="btn-primary">Butuh Bantuan</button>
    </div>

    <!-- Custom Input -->
    <div class="bg-white p-6 rounded-2xl shadow-lg flex flex-col md:flex-row gap-4">
        <input type="text" id="customText"
            class="flex-1 p-4 rounded-xl border focus:outline-none focus:ring-2 focus:ring-blue-500"
            placeholder="Ketik pesan di sini...">

        <button onclick="speakCustom()"
            class="bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700 transition shadow-md font-semibold">
            Ucapkan
        </button>
    </div>

</div>

<script>
function speak(text) {
    document.getElementById("outputText").innerText = text;
    const speech = new SpeechSynthesisUtterance(text);
    speech.lang = "id-ID";
    window.speechSynthesis.speak(speech);
}

function speakCustom() {
    const text = document.getElementById("customText").value;
    if(text.trim() !== "") {
        speak(text);
        document.getElementById("customText").value = "";
    }
}

function goBack() {
    window.history.back();
}
</script>

<style>
.btn-primary {
    background: white;
    padding: 16px;
    border-radius: 20px;
    font-weight: 600;
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
}

.btn-primary:hover {
    background: #2563eb;
    color: white;
    transform: translateY(-5px) scale(1.03);
    box-shadow: 0 12px 25px rgba(37,99,235,0.4);
}
</style>

</body>
</html>