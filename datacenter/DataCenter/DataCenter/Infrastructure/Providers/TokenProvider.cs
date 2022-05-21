using DataCenter.Infrastructure.Providers.Interfaces;
using System;

namespace DataCenter.Infrastructure.Providers
{
    public class TokenProvider : ITokenProvider, IProvider
    {
        public string GenerateAuthToken()
        {
            return Guid.NewGuid().ToString();
        }
    }
}
