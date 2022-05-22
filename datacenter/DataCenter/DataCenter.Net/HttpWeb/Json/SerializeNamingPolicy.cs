using System.Text.Json;

namespace DataCenter.Network.HttpWeb.Json
{
    public class SerializeNamingPolicy : JsonNamingPolicy
    {
        public override string ConvertName(string name) =>
            name.Substring(0, 1).ToLower() + name.Substring(1);
    }
}
