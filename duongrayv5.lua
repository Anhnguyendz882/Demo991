local Rayfield = loadstring(game:HttpGet('https://sirius.menu/rayfield'))()

-- TẠO CỬA SỔ CHÍNH
local Window = Rayfield:CreateWindow({
   Name = "🔥 KN | SIU NHAN GAO",
   LoadingTitle = "Đang khởi chạy hệ thống bảo mật...",
   LoadingSubtitle = "KN",
   ConfigurationSaving = {
      Enabled = true,
      FolderName = "GeminiScripts",
      FileName = "Settings"
   },
   Discord = {
      Enabled = false,
      Invite = "",
      RememberJoins = true
   },
   KeySystem = false -- Bạn có thể bật nếu muốn làm key
})

-- BIẾN HỆ THỐNG
local Aiming = false
local FOVRadius = 150
local ReachDistance = 25
local ItemESP_Enabled = false

-- VÒNG TRÒN FOV (Cải tiến màu sắc)
local FOV_Circle = Drawing.new("Circle")
FOV_Circle.Thickness = 2
FOV_Circle.Color = Color3.fromRGB(0, 255, 255) -- Màu xanh Neon
FOV_Circle.Filled = false
FOV_Circle.Visible = false
FOV_Circle.Transparency = 0.7

-- ==========================================
-- TAB: CHIẾN ĐẤU (COMBAT)
-- ==========================================
local CombatTab = Window:CreateTab("⚔️ Combat", 4483362458)

CombatTab:CreateSection("Melee & Aim Settings")

CombatTab:CreateToggle({
   Name = "Kill Aura (Đánh là dính)",
   CurrentValue = false,
   Flag = "KillAura",
   Callback = function(Value)
      Aiming = Value
      if Value then
         Rayfield:Notify({Title = "Combat", Content = "Đã bật Kill Aura!", Duration = 2, Image = 4483362458})
      end
   end,
})

CombatTab:CreateSlider({
   Name = "Phạm vi đánh (Reach)",
   Range = {10, 100},
   Increment = 1,
   CurrentValue = 25,
   Flag = "ReachSlider",
   Callback = function(Value)
      ReachDistance = Value
   end,
})

CombatTab:CreateSection("FOV Settings")

CombatTab:CreateToggle({
   Name = "Hiện vòng tròn FOV",
   CurrentValue = false,
   Flag = "ShowFOV",
   Callback = function(Value)
      FOV_Circle.Visible = Value
   end,
})

CombatTab:CreateColorPicker({
    Name = "Màu vòng FOV",
    Color = Color3.fromRGB(0, 255, 255),
    Callback = function(Value)
        FOV_Circle.Color = Value
    end,
})

-- ==========================================
-- TAB: HIỂN THỊ (ESP)
-- ==========================================
local ESPTab = Window:CreateTab("👁️ ESP & Visuals", 4483362458)

ESPTab:CreateSection("Item ESP (Ảnh của bạn)")

local function UpdateESP()
    local itemsToESP = {"Vỏ Súng Máy", "Đạn trung bình", "Đạn nhẹ", "Dr. Ricco's", "Đạn Quý giá", "Cái xẻng", "Vật liệu"}
    for _, v in pairs(game.Workspace:GetDescendants()) do
        if ItemESP_Enabled then
            for _, itemName in pairs(itemsToESP) do
                if v.Name == itemName and not v:FindFirstChild("ItemESP") then
                    local bbg = Instance.new("BillboardGui", v)
                    bbg.Name = "ItemESP"
                    bbg.AlwaysOnTop = true
                    bbg.Size = UDim2.new(0, 100, 0, 40)
                    
                    local txt = Instance.new("TextLabel", bbg)
                    txt.Size = UDim2.new(1, 0, 1, 0)
                    txt.BackgroundTransparency = 1
                    txt.Text = "💎 " .. itemName
                    txt.TextColor3 = Color3.fromRGB(0, 255, 127) -- Màu xanh ngọc
                    txt.TextStrokeTransparency = 0
                    txt.TextSize = 12
                    txt.Font = Enum.Font.GothamBold
                end
            end
        else
            if v:FindFirstChild("ItemESP") then v.ItemESP:Destroy() end
        end
    end
end

ESPTab:CreateToggle({
   Name = "Bật ESP Vật phẩm",
   CurrentValue = false,
   Flag = "ItemESP",
   Callback = function(Value)
      ItemESP_Enabled = Value
      UpdateESP()
   end,
})

ESPTab:CreateButton({
   Name = "Làm mới ESP (Refresh)",
   Callback = function()
      UpdateESP()
   end,
})

-- ==========================================
-- TAB: NGƯỜI CHƠI (PLAYER)
-- ==========================================
local PlayerTab = Window:CreateTab("👤 Player", 4483362458)

PlayerTab:CreateSlider({
   Name = "Tốc độ chạy (Speed)",
   Range = {16, 300},
   Increment = 1,
   CurrentValue = 16,
   Callback = function(Value)
      game.Players.LocalPlayer.Character.Humanoid.WalkSpeed = Value
   end,
})

PlayerTab:CreateSlider({
   Name = "Độ cao nhảy (Jump)",
   Range = {50, 500},
   Increment = 1,
   CurrentValue = 50,
   Callback = function(Value)
      game.Players.LocalPlayer.Character.Humanoid.JumpPower = Value
   end,
})

-- ==========================================
-- VÒNG LẶP XỬ LÝ CHÍNH (LOGIC)
-- ==========================================
game:GetService("RunService").RenderStepped:Connect(function()
    local mouseLoc = game:GetService("UserInputService"):GetMouseLocation()
    FOV_Circle.Position = mouseLoc

    if Aiming then
        for _, p in pairs(game.Players:GetPlayers()) do
            if p ~= game.Players.LocalPlayer and p.Character and p.Character:FindFirstChild("HumanoidRootPart") then
                local hrp = p.Character.HumanoidRootPart
                local screenPos, onScreen = game.Workspace.CurrentCamera:WorldToViewportPoint(hrp.Position)
                
                if onScreen then
                    local distMouse = (Vector2.new(screenPos.X, screenPos.Y) - mouseLoc).Magnitude
                    if distMouse <= FOV_Circle.Radius then
                        local distPlayer = (game.Players.LocalPlayer.Character.HumanoidRootPart.Position - hrp.Position).Magnitude
                        if distPlayer <= ReachDistance then
                            local tool = game.Players.LocalPlayer.Character:FindFirstChildOfClass("Tool")
                            if tool and tool:FindFirstChild("Handle") then
                                -- Thực thi đánh từ xa
                                firetouchinterest(hrp, tool.Handle, 0)
                                task.wait()
                                firetouchinterest(hrp, tool.Handle, 1)
                            end
                        end
                    end
                end
            end
        end
    end
end)

Rayfield:Notify({Title = "KN", Content = "Script đã tải xong. Chúc bạn chơi game vui vẻ!", Duration = 5})
