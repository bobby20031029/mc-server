import requests

# 你的 CurseForge API Key
API_KEY = "$2a$10$KXc2njASUX/4geTVZmjb4Ox6czGQVAKXUcQcSATtUqGnuchFJwSf2"  # 这里替换成你的真实 API Key

# CurseForge API 请求 URL（按下载量降序排列，获取前10个Mod）
URL = "https://api.curseforge.com/v1/mods/search?gameId=432&sortField=2&sortOrder=desc&pageSize=10"

# 请求头（包含 API Key）
HEADERS = {
    "X-Api-Key": API_KEY,
    "User-Agent": "MinecraftModHeatmap-Test/1.0"
}

# 发送 GET 请求
response = requests.get(URL, headers=HEADERS)

# 检查 API 响应状态
if response.status_code == 200:
    data = response.json()  # 解析 JSON 数据
    mods = data.get("data", [])  # 获取 Mod 列表
    
    if mods:
        print("\n✅ 获取到的前10个 Mod:")
        print("=" * 50)
        for idx, mod in enumerate(mods, start=1):
            mod_name = mod.get("name", "未知Mod")
            download_count = mod.get("downloadCount", 0)
            print(f"{idx}. {mod_name} - 下载量: {download_count:,}")
    else:
        print("\n❌ 没有获取到 Mod 数据")
else:
    print(f"\n❌ API 请求失败: {response.status_code}")
    print("响应内容:", response.text)
